#include "LangAnalyzer.hpp"
#include "Util.hpp"

LangAnalyzer::LangAnalyzer(const std::string& baselang, const std::string& extralang,
                           const std::string& sourcedir, const std::string& mode)
: theBasisLanguage{baselang},
  theExtraLanguage{extralang},
  theSourceDir{sourcedir},
  theMode{mode}
{
   // Directories must have a slash at the end.
   if (*theSourceDir.rbegin() != '/')
   {
      theSourceDir.push_back('/');
   }
}

Util::Error LangAnalyzer::generate()
{
   // Read the first lang file.
   std::stringstream firstNewLangPath;
   firstNewLangPath << theSourceDir << theBasisLanguage << ".lang";
   if (!readLangFile(firstNewLangPath.str(), theBasis, theOffsets, theDataOffsets))
   {
      return Util::InvalidLangFile;
   }

   // Read the second lang file.
   std::stringstream secondNewLangPath;
   secondNewLangPath << theSourceDir << theExtraLanguage << ".lang";
   if (!readExtraLangFile(secondNewLangPath.str(), theExtraLang))
   {
      return Util::InvalidLangFile;
   }
   
   // Remove junk from the first language, and assigns second language strings to it.
   filter(theBasis, theOffsets, theExtraLang, theDataOffsets);
   
   // Stores the records that were added or modified in the new version.
   for (LangUnit* i = &theBasis[0]; i < &theBasis[theBasis.size()]; ++i)
   {
      LangUnit unit;
      std::pair<UnitIterator, UnitIterator> result = theOffsets.equal_range(i->record.offset);
      for (UnitIterator m = result.first; m != result.second; ++m)
      {
         if (m->second == *i)
         {
            unit = m->second;
            break;
         }
      }

      if (!unit.text.empty())
      {
         theUnits.push_back(unit);
      }
   }

   // Read the target lang file.
   std::stringstream unofficialLangPath;
   unofficialLangPath << theSourceDir << "target.lang.csv";
   if (readLangCsvFile(unofficialLangPath.str(), theTargetLang))
   {
      // Assign translated strings.
      translate(theUnits, theTargetLang);
   }
   
   // Write new_lang_records.sql
   if (!writeNewRecordsSqlFile())
   {
      return Util::CannotWriteFiles;
   }
   
   return Util::Success;
}

void LangAnalyzer::filter(std::vector<LangUnit>& basis, std::multimap<unsigned int, LangUnit>& offsets,
                          std::map<unsigned long long, LangUnit>& extraLang, DataOffset& dataOffset)
{
   for (std::set<unsigned int>::iterator i = dataOffset.begin(); i != dataOffset.end(); ++i)
   {
      std::pair<UnitIterator, UnitIterator> result = offsets.equal_range(*i);
      if (result.first != result.second)
      {
         bool areEqual = true;
         std::string text1 = result.first->second.text; // First language
         std::string text2; // Second language

         for (UnitIterator m = result.first; m != result.second; ++m)
         {
            OffsetIterator g = extraLang.find(m->second.key);
            if (g != extraLang.end())
            {
               text2 = g->second.text;
               areEqual &= (text1.compare(text2) == 0);
            }
         }

         for (UnitIterator m = result.first; m != result.second; ++m)
         {
            if (areEqual)
            {
               m->second.text.clear();
            }
            else
            {
               m->second.text2 = text2;
            }
         }
      }
   }
}

void LangAnalyzer::translate(std::vector<LangUnit>& basis, std::map<unsigned long long, LangUnit>& extraLang)
{
   for (auto& unit : basis)
   {
      auto i = extraLang.find(unit.key);
      if (i != extraLang.end())
      {
         unit.text3 = i->second.text;
      }
   }
}

bool LangAnalyzer::readLangFile(const std::string& file, std::vector<LangUnit>& basis,
                                std::multimap<unsigned int, LangUnit>& offsets,
                                DataOffset& dataOffset)
{
   bool result = false;
   const unsigned int HEADER_ID = 0x00000002;

   // Read file content.
   std::ifstream ifs(file.c_str(), std::ifstream::in | std::ifstream::binary);
   if (ifs.good())
   {
      // Offset where the first string is.
      size_t stringOffset = 0;

      // Read the header ID.
      unsigned int headerID = 0;
      ifs.read(reinterpret_cast<char*>(&headerID), sizeof(unsigned int));
      headerID = ntohl(headerID);
      stringOffset += sizeof(unsigned int);

      // Check the header.
      if (headerID == HEADER_ID)
      {
         // Read the number of records.
         unsigned int recordCount = 0;
         ifs.read(reinterpret_cast<char*>(&recordCount), sizeof(unsigned int));
         recordCount = ntohl(recordCount);
         stringOffset += sizeof(unsigned int);

         // Read all the records.
         for (size_t i = 0; i < recordCount; ++i)
         {
            Record r;
            ifs.read(reinterpret_cast<char*>(&r), sizeof(unsigned int) * 4);
            r.tableid = ntohl(r.tableid);
            r.seqid = ntohl(r.seqid);
            r.stringid = ntohl(r.stringid);
            r.offset = ntohl(r.offset);

            LangUnit unit;
            unit.record = r;
            unit.key = createKey(r);

            basis.push_back(unit);

            // Update offset
            stringOffset += sizeof(unsigned int) * 4;
         }

         // Read all the strings.
         for (LangUnit* i = &basis[0]; i < &basis[recordCount]; ++i)
         {
            char c;
            ifs.seekg(stringOffset + i->record.offset, ifs.beg);
            do
            {
               ifs.read(&c, sizeof(char));
               if (c != 0 && c != 0xd)
               {
                  i->text.push_back(c);
               }
            } while (c != 0);
         }

         // Delete duplicated strings.
         for (LangUnit* i = &basis[0]; i < &basis[recordCount]; ++i)
         {
            // If there is an inserted record with the same offset as i then clear i string.
            if (offsets.find(i->record.offset) != offsets.end())
            {
               i->text.clear();
            }
            offsets.insert(std::pair<unsigned int, LangUnit>(i->record.offset, *i));
            dataOffset.insert(i->record.offset);
         }

         ifs.close();

         result = true;
      }
   }

   return result;
}

bool LangAnalyzer::readExtraLangFile(const std::string& file, std::map<unsigned long long, LangUnit>& extraLang)
{
   bool result = false;
   const unsigned int HEADER_ID = 0x00000002;

   // Read file content.
   std::ifstream ifs(file.c_str(), std::ifstream::in | std::ifstream::binary);
   if (ifs.good())
   {
      // Offset where the first string is.
      size_t stringOffset = 0;

      // Read the header ID.
      unsigned int headerID = 0;
      ifs.read(reinterpret_cast<char*>(&headerID), sizeof(unsigned int));
      headerID = ntohl(headerID);
      stringOffset += sizeof(unsigned int);

      // Check the header.
      if (headerID == HEADER_ID)
      {
         // Read the number of records.
         unsigned int recordCount = 0;
         ifs.read(reinterpret_cast<char*>(&recordCount), sizeof(unsigned int));
         recordCount = ntohl(recordCount);
         stringOffset += sizeof(unsigned int);

         // Read all the records.
         std::vector<LangUnit> records;
         for (size_t i = 0; i < recordCount; ++i)
         {
            Record r;
            ifs.read(reinterpret_cast<char*>(&r), sizeof(unsigned int) * 4);
            r.tableid = ntohl(r.tableid);
            r.seqid = ntohl(r.seqid);
            r.stringid = ntohl(r.stringid);
            r.offset = ntohl(r.offset);

            LangUnit unit;
            unit.record = r;
            unit.key = createKey(r);

            records.push_back(unit);

            // Update offset
            stringOffset += sizeof(unsigned int) * 4;
         }

         // Read all the strings.
         for (LangUnit* i = &records[0]; i < &records[recordCount]; ++i)
         {
            char c;
            ifs.seekg(stringOffset + i->record.offset, ifs.beg);
            do
            {
               ifs.read(&c, sizeof(char));
               if (c != 0 && c != 0xd)
               {
                  i->text.push_back(c);
               }
            } while (c != 0);

            // Store the translation unit and its key.
            extraLang[i->key] = *i;
         }

         ifs.close();

         result = true;
      }
   }

   return result;
}

bool LangAnalyzer::readLangCsvFile(const std::string& file, std::map<unsigned long long, LangUnit>& targetLang)
{
   // No magic numbers.
   enum 
   {
      ID,
      Unknown,
      Index,
      Offset,
      Text,
      FullLine
   };
   
   bool result{};

   // Read file content.
   std::ifstream ifs(file.c_str(), std::ifstream::in | std::ifstream::binary);
   if (ifs.good())
   {
      std::string header;
      std::getline(ifs, header);
      header.erase(std::remove(header.begin(), header.end(), '\r'), header.end());
      if (header == "\"ID\",\"Unknown\",\"Index\",\"Offset\",\"Text\"")
      {
         while (ifs.good())
         {
            std::string line;
            std::getline(ifs, line);
            line.erase(std::remove(line.begin(), line.end(), '\r'), line.end());
            std::regex regex(R"((?:"[0-9]+")|".+")");
            std::vector<std::string> fields{std::sregex_token_iterator(line.begin(), line.end(), regex, 0), 
                                            std::sregex_token_iterator()};

            if (fields.size() == FullLine)
            {
               LangUnit u;

               // Remove first and last quote of every string.
               u.record.tableid = std::stoul(fields[ID].substr(1, fields[ID].size() - 2));
               u.record.seqid = std::stoul(fields[Unknown].substr(1, fields[Unknown].size() - 2));
               u.record.stringid = std::stoul(fields[Index].substr(1, fields[Index].size() - 2));
               u.record.offset = std::stoul(fields[Offset].substr(1, fields[Offset].size() - 2));
               u.text = fields[Text].substr(1, fields[Text].size() - 2);
               Util::replaceAll(u.text, "\"\"", "\"");
               Util::replaceAll(u.text, "\\n", "\n");
               u.key = createKey(u.record);

               targetLang[u.key] = u;
            }
         }
         
         result = true;
      }
   }

   return result;
}

bool LangAnalyzer::writeNewRecordsSqlFile()
{
   const size_t recordsPerFile = 2048;
   const size_t recordsPerQuery = 16;

   for (size_t n = 0; n < theUnits.size() / recordsPerFile + 1; ++n)
   {
      std::stringstream aNewRecordsPath;
      aNewRecordsPath << theSourceDir << "new_lang_records_"<< std::setw(3) << std::setfill('0') << n << ".sql";
      std::ofstream ofs1(aNewRecordsPath.str().c_str(), std::ifstream::out | std::ifstream::binary);
      if (ofs1.good())
      {
         size_t start = n * recordsPerFile;
         size_t end = start + recordsPerFile > theUnits.size() ? theUnits.size() : start + recordsPerFile;
         size_t records{};
         LangUnit* u = &theUnits[end]; u--;
         for (LangUnit* i = &theUnits[start]; i < &theUnits[end]; ++i)
         {
            Util::replaceAll(i->text, "'", "\\'");
            Util::replaceAll(i->text2, "'", "\\'");
            if (records == 0)
            {
               if (theMode == "update")
               {
                  ofs1 << "INSERT INTO `NewLang` (`TableId`, `TextId`, `SeqId`, `Fr`, `En`) VALUES";
               }
               else
               {
                  ofs1 << "INSERT INTO `Lang` (`TableId`, `TextId`, `SeqId`, `Fr`, `En`, `Es`, `IsTranslated`) VALUES";
               }
            }
            
            std::string es{"NULL"};
            if (!i->text3.empty())
            {
               Util::replaceAll(i->text3, "'", "\\'");
               es = "'" + i->text3 + "'";
            }
            ofs1 << "(" << i->record.tableid << "," 
                        << i->record.stringid << ","
                        << i->record.seqid << ","
                        << "'" << i->text << "',"
                        << "'" << i->text2;
            if (theMode == "setup")
            {
               ofs1 << "'," << es << "," << (es == "NULL" ? 0 : 1) << ")";
            }
            else
            {
               ofs1 << "')";
            }

            ofs1 << (records == recordsPerQuery - 1 || (end == theUnits.size() && i == u) ? ";\n" : ",\n");
            if (++records == recordsPerQuery)
            {
               records = 0;
            }
         }
      }
      else
      {
         return false;
      }
   }
   
   return true;
}

unsigned long long LangAnalyzer::createKey(const Record& record)
{
   unsigned long long tableid = record.tableid;
   tableid <<= 32;
   unsigned long long textid = record.stringid;
   textid <<= 8;
   unsigned long long seqid = record.seqid;

   return tableid | textid | seqid;
}
