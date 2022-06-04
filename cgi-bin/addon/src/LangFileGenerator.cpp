#include "LangFileGenerator.hpp"

LangFileGenerator::LangFileGenerator(const std::string& language, const std::string& extralang,
                                     const std::string& sourcedir, const std::string& destdir)
: theOfficialLanguage{language},
  theExtraLanguage{extralang},
  theSourceDir{sourcedir},
  theDestDir{destdir},
  theHeaderID{},
  theRecordCount{}
{
   // Directories must have a slash at the end.
   if (*theSourceDir.rbegin() != '/')
   {
      theSourceDir.push_back('/');
   }
   if (*theDestDir.rbegin() != '/')
   {
      theDestDir.push_back('/');
   }
}

LangFileGenerator::~LangFileGenerator()
{
   for (OffsetIteratorPtr i = theExtraLang.begin(); i != theExtraLang.end(); ++i)
   {
      delete i->second;
   }
}

Util::Error LangFileGenerator::generate(const std::string& language)
{
   theTargetLanguage = language;

   // Read the translation file.
   if (!readTranslationFile(theDestDir + "db_lang.tmp"))
   {
      return Util::Invalid_db_lang_tmp_File;
   }

   // Read an official lang file.
   if (!readLangFile(theSourceDir + theOfficialLanguage + ".lang"))
   {
      return Util::InvalidLangFile;
   }
   
   // Read the other official lang file.
   if (!readExtraLangFile(theSourceDir + theExtraLanguage + ".lang"))
   {
      return Util::InvalidLangFile;;
   }
   
   // Offset where the first string is.
   std::size_t stringOffset{};

   // Step 1. Update record offset
   if (!theBasis.empty())
   {
      for (LangUnit* i = &theBasis[0]; i < &theBasis[theRecordCount]; ++i)
      {
         // If there is no translation, take a string from the official language and update offset.
         if (theTranslation.count(i->key) == 0)
         {
            bool isAdded{};
            std::size_t offsetInc{};
            std::pair<UnitIterator, UnitIterator> result{theOffsets.equal_range(i->record.offset)};
            OffsetIteratorPtr e{theExtraLang.find(i->key)};
            for (UnitIterator m = result.first; m != result.second; ++m)
            {
               if (m->second.step == 0)
               {
                  m->second.record.offset = stringOffset;
                  m->second.step = 1;
                  if (!isAdded)
                  {
                     if (!m->second.text.empty())
                     {
                        if (e != theExtraLang.end())
                        {
                           if (!e->second->text.empty())
                           {
                              m->second.text = e->second->text;
                              offsetInc = m->second.text.size() + 1; // 0 must be set at the end.
                              isAdded = true;
                           }
                        }
                        else
                        {
                           offsetInc = m->second.text.size() + 1; // 0 must be set at the end.
                           isAdded = true;
                        }
                     }
                  }
               }
            }
            stringOffset += offsetInc;
         }
         // If there is translation, take it and update offset.
         else
         {
            bool isAdded{};
            std::size_t offsetInc{};
            std::pair<UnitIterator, UnitIterator> result{theOffsets.equal_range(i->record.offset)};
            OffsetIterator e = theTranslation.find(i->key);
            for (UnitIterator m = result.first; m != result.second; ++m)
            {
               if (m->second.step == 0)
               {
                  m->second.record.offset = stringOffset;
                  m->second.step = 1;
                  // Assign translated text.
                  if (!isAdded)
                  {
                     if (!m->second.text.empty() && !e->second.text.empty())
                     {
                        m->second.text = e->second.text;
                        offsetInc = m->second.text.size() + 1; // 0 must be recorded at the end.
                        isAdded = true;
                     }
                  }
               }
            }
            stringOffset += offsetInc;
         }
      }
   }

   // Create the translated lang file.
   std::ofstream ofs{theDestDir + theTargetLanguage + ".lang", std::ifstream::out | std::ifstream::binary};
   if (ofs.good())
   {
      // Write header ID.
      unsigned int aHeaderID{htonl(theHeaderID)};
      ofs.write(reinterpret_cast<char*> (&aHeaderID), sizeof ( unsigned int));

      // Write number of records.
      unsigned int aRecordCount{htonl(theRecordCount)};
      ofs.write(reinterpret_cast<char*> (&aRecordCount), sizeof ( unsigned int));

      // Write record identifiers.
      for (LangUnit* i = &theBasis[0]; i < &theBasis[theRecordCount]; ++i)
      {
         bool isInserted{};
         std::pair<UnitIterator, UnitIterator> result{theOffsets.equal_range(i->record.offset)};
         for (UnitIterator m = result.first; m != result.second; ++m)
         {
            if (m->second.key == i->key && !isInserted)
            {
               Record& r = m->second.record;
               r.tableid = htonl(r.tableid);
               r.seqid = htonl(r.seqid);
               r.stringid = htonl(r.stringid);
               r.offset = htonl(r.offset);
               ofs.write(reinterpret_cast<char*> (&r), sizeof (unsigned int) * 4);
               isInserted = true;
            }
            // First step is completed for this record.
            m->second.step = 1;
         }
      }

      // Write strings.
      for (LangUnit* i = &theBasis[0]; i < &theBasis[theRecordCount]; ++i)
      {
         bool isInserted{};
         std::pair<UnitIterator, UnitIterator> result{theOffsets.equal_range(i->record.offset)};
         for (UnitIterator m = result.first; m != result.second; ++m)
         {
            if (m->second.step == 1)
            {
               if (!m->second.text.empty() && !isInserted)
               {
                  const char* cstr = m->second.text.c_str();
                  ofs.write(cstr, sizeof (char) * m->second.text.size());
                  char c = 0;
                  ofs.write(&c, sizeof (char));
                  isInserted = true;
               }
               // Second step is completed for this record.
               m->second.step = 2;
            }
         }
      }

      ofs.close();
   }
   else
   {
      return Util::CannotWriteFiles;
   }

   return Util::Success;
}

bool LangFileGenerator::readLangFile(const std::string& file)
{
   bool result{};
   const unsigned int HeaderId{0x00000002};

   // Read file content.
   std::ifstream ifs{file.c_str(), std::ifstream::in | std::ifstream::binary};
   if (ifs.good())
   {
      // Offset where the first string is.
      std::size_t stringOffset{};

      // Read the header ID.
      ifs.read(reinterpret_cast<char*> (&theHeaderID), sizeof (unsigned int));
      theHeaderID = ntohl(theHeaderID);
      stringOffset += sizeof (unsigned int);

      // Check the header.
      if (theHeaderID == HeaderId)
      {
         // Read the number of records.
         ifs.read(reinterpret_cast<char*> (&theRecordCount), sizeof (unsigned int));
         theRecordCount = ntohl(theRecordCount);
         stringOffset += sizeof (unsigned int);

         // Read all the records.
         for (std::size_t i = 0; i < theRecordCount; ++i)
         {
            Record r;
            ifs.read(reinterpret_cast<char*> (&r), sizeof (unsigned int) * 4);
            r.tableid = ntohl(r.tableid);
            r.seqid = ntohl(r.seqid);
            r.stringid = ntohl(r.stringid);
            r.offset = ntohl(r.offset);

            LangUnit unit;
            unit.record = r;
            unit.step = 0;
            unit.key = createKey(r);

            theBasis.push_back(unit);

            // Update offset
            stringOffset += sizeof (unsigned int) * 4;
         }

         // Read all the strings.
         for (LangUnit* i = &theBasis[0]; i < &theBasis[theRecordCount]; ++i)
         {
            char c;
            ifs.seekg(stringOffset + i->record.offset, ifs.beg);
            do
            {
               ifs.read(&c, sizeof ( char));
               if (c != 0 && c != 0xd)
               {
                  i->text.push_back(c);
               }
            }
            while (c != 0);
         }

         // Delete duplicated strings.
         for (LangUnit* i = &theBasis[0]; i < &theBasis[theRecordCount]; ++i)
         {
            // If there is an inserted record with the same offset as i then clear i string.
            if (theOffsets.find(i->record.offset) != theOffsets.end())
            {
               i->text.clear();
            }
            theOffsets.insert({i->record.offset, *i});
         }

         ifs.close();

         result = true;
      }
   }

   return result;
}

bool LangFileGenerator::readExtraLangFile(const std::string& file)
{
   bool result{};
   const unsigned int HeaderId{0x00000002};

   // Read file content.
   std::ifstream ifs(file.c_str(), std::ifstream::in | std::ifstream::binary);
   if (ifs.good())
   {
      // Offset where the first string is.
      std::size_t stringOffset{};

      // Read the header ID.
      unsigned int headerID{};
      ifs.read(reinterpret_cast<char*> (&headerID), sizeof (unsigned int));
      headerID = ntohl(headerID);
      stringOffset += sizeof (unsigned int);

      // Check the header.
      if (headerID == HeaderId)
      {
         // Read the number of records.
         unsigned int recordCount{};
         ifs.read(reinterpret_cast<char*> (&recordCount), sizeof (unsigned int));
         recordCount = ntohl(recordCount);
         stringOffset += sizeof (unsigned int);

         // Read all the records.
         std::vector<LangUnit*> records;
         for (std::size_t i = 0; i < recordCount; ++i)
         {
            Record r;
            ifs.read(reinterpret_cast<char*> (&r), sizeof (unsigned int) * 4);
            r.tableid = ntohl(r.tableid);
            r.seqid = ntohl(r.seqid);
            r.stringid = ntohl(r.stringid);
            r.offset = ntohl(r.offset);

            LangUnit* unit = new LangUnit();
            unit->record = r;
            unit->step = 0;
            unit->key = createKey(r);

            records.push_back(unit);

            // Update offset
            stringOffset += sizeof (unsigned int) * 4;
         }

         // Read all the strings.
         for (LangUnit** i = &records[0]; i < &records[recordCount]; ++i)
         {
            char c;
            ifs.seekg(stringOffset + (*i)->record.offset, ifs.beg);
            do
            {
               ifs.read(&c, sizeof ( char));
               if (c != 0 && c != 0xd)
               {
                  (*i)->text.push_back(c);
               }
            }
            while (c != 0);
            
            // Store the translation unit and its key.
            theExtraLang[(*i)->key] = (*i);
         }
         
         ifs.close();

         result = true;
      }
   }

   return result;
}

bool LangFileGenerator::readTranslationFile(const std::string& file)
{
   bool result = false;

   // Read file content and store it into contents string.
   std::ifstream ifs(file.c_str(), std::ifstream::in | std::ifstream::binary);
   if (ifs.good())
   {
      char c;
      std::string contents;

      do
      {
         ifs.read(&c, sizeof (char));
         if (c != 0 && c != 0xd)
         {
            contents.push_back(c);
         }
      }
      while (!ifs.eof());

      ifs.close();

      // Process contents until every string was read and stored.
      std::size_t pos = 0;
      while (!contents.empty())
      {
         // Find three identifiers.
         std::size_t i1 = contents.find("{{", pos);
         std::size_t i2 = contents.find("}}", pos);
         if (i1 != std::string::npos && i2 != std::string::npos)
         {
            std::string id = contents.substr(i1, i2 - i1 + 2); // +2 because of "}}" length.
            // Remove curly brackets.
            id = id.substr(2, id.size() - 4);
            i1 = id.find(",");
            std::string tableidStr = id.substr(0, i1);
            id = id.substr(i1 + 1, id.size() - i1);
            i1 = id.find(",");
            std::string seqidStr = id.substr(0, i1);
            std::string stringidStr = id.substr(i1 + 1, id.size() - i1);

            // Store data in a register.
            LangUnit unit;
            std::stringstream ss1(tableidStr);
            ss1 >> std::hex >> unit.record.tableid;
            std::stringstream ss2(seqidStr);
            ss2 >> std::dec >> unit.record.seqid;
            std::stringstream ss3(stringidStr);
            ss3 >> std::dec >> unit.record.stringid;

            // Skip processed strings and reads the translated text.
            pos = contents.find("}}", pos) + 2; // +2 because of "}}" length.
            std::size_t i3 = contents.find("{{", pos) - pos;
            std::string text = (i3 != std::string::npos ? contents.substr(pos, i3) : contents);
            if (i3 != std::string::npos)
            {
               pos += i3;
            }
            else
            {
               contents.clear();
            }

            // Remove \n from the beginning and the end.
            while (text[0] == '\n')
            {
               text = text.substr(1, text.size() - 1);
            }
            while (text[text.size() - 1] == '\n')
            {
               text = text.substr(0, text.size() - 1);
            }
            unit.text = text;

            // Patch text for Spanish and Portuguese language.
            if (theTargetLanguage == "es" || theTargetLanguage == "br" || theTargetLanguage == "pt")
            {
               Util::replaceAll(unit.text, "a <<", "æ <<");
               Util::replaceAll(unit.text, "a |c", "æ |c");
            }
            
            // Create a key for this translation unit and store it.
            unit.key = createKey(unit.record);
            theTranslation[unit.key] = unit;
         }
         else
         {
            contents.clear();
         }
      }

      result = true;
   }

   return result;
}

unsigned long long LangFileGenerator::createKey(const Record& record) const
{
   unsigned long long tableid{record.tableid};
   tableid <<= 32;
   unsigned long long textid{record.stringid};
   textid <<= 8;
   unsigned long long seqid{record.seqid};

   return tableid | textid | seqid;
}
