#include "LuaAnalyzer.hpp"

LuaAnalyzer::LuaAnalyzer(const std::string& baselang, const std::string& extralang,
                         const std::string& sourcedir, const std::string& mode)
: theBasisLanguage(baselang),
  theExtraLanguage(extralang),
  theSourceDir(sourcedir),
  theMode{mode}
{
   // Directories must have a slash at the end.
   if (*theSourceDir.rbegin() != '/')
   {
      theSourceDir.push_back('/');
   }
}

Util::Error LuaAnalyzer::generate()
{
   // Read the first client.lua file
   std::stringstream firstNewClientLuaPath;
   firstNewClientLuaPath << theSourceDir << theBasisLanguage << "_client.lua";
   if (!readLuaFile(firstNewClientLuaPath.str(), theUnits, theBasisLanguage))
   {
      return Util::InvalidClientOrPregameFile;
   }

   // Read the first pregame.lua file
   std::stringstream firstNewPregameLuaPath;
   firstNewPregameLuaPath << theSourceDir << theBasisLanguage << "_pregame.lua";
   if (!readLuaFile(firstNewPregameLuaPath.str(), theUnits, theBasisLanguage))
   {
      return Util::InvalidClientOrPregameFile;
   }

   // Read the second client.lua file
   std::stringstream secondNewClientLuaPath;
   secondNewClientLuaPath << theSourceDir << theExtraLanguage << "_client.lua";
   if (!readLuaFile(secondNewClientLuaPath.str(), theUnits, theExtraLanguage, true))
   {
      return Util::InvalidClientOrPregameFile;
   }

   // Read the second pregame.lua file
   std::stringstream secondNewPregameLuaPath;
   secondNewPregameLuaPath << theSourceDir << theExtraLanguage << "_pregame.lua";
   if (!readLuaFile(secondNewPregameLuaPath.str(), theUnits, theExtraLanguage, true))
   {
      return Util::InvalidClientOrPregameFile;
   }

   // Write new_lua_records.sql
   if (!writeNewRecordsSqlFile())
   {
      return Util::CannotWriteFiles;
   }

   return Util::Success;
}

bool LuaAnalyzer::readLuaFile(const std::string& file, std::map<std::string, LuaUnit>& units,
                              const std::string& language, bool isSecondLanguage)
{
   bool result = false;

   // Read file content.
   std::ifstream ifs(file.c_str(), std::ifstream::in | std::ifstream::binary);
   if (ifs.good())
   {
      // Read the whole file.
      std::vector<std::string> texts;
      while (!ifs.eof())
      {
         std::string str;
         std::getline(ifs, str);
         texts.push_back(str);
      }
      ifs.close();

      // Process the file.
      for (size_t i = 0; i < texts.size(); ++i)
      {
         std::string& str = texts[i];
         if (str.find("SafeAddString") != std::string::npos)
         {
            LuaUnit unit;

            // Remove "SafeAddString".
            str.erase(0, 13);

            // Remove first parenthesis.
            size_t pos = str.find_first_of("(");
            if (pos != std::string::npos)
            {
               str.erase(pos, 1);
            }
            // Remove last parenthesis.
            pos = str.find_last_of(")");
            if (pos != std::string::npos)
            {
               str.erase(pos, 1);
            }

            // Find ID and store it.
            pos = str.find_first_of(",");
            unit.id = str.substr(0, pos);

            // Find text and store it.
            size_t pos1 = str.find_first_of("\"");
            size_t pos2 = str.find_last_of("\"");
            unit.text = str.substr(pos1 + 1, pos2 - pos1 - 1);
            // Replace "\n" with line feed character.
            Util::replaceAll(unit.text, "\\n", "\n");

            // Find and store version number of the string.
            pos = str.find_last_of(",");
            std::string temp = str.substr(pos, str.size() - pos);
            std::string temp2;
            for (size_t n = 0; n < temp.size(); ++n)
            {
               if (std::isdigit(temp[n]))
               {
                  temp2.push_back(temp[n]);
               }
            }
            std::stringstream ss;
            ss.str(temp2);
            int n;
            ss >> n;
            unit.version = n;

            // Store the translation unit for the first language.
            if (!isSecondLanguage)
            {
               units[unit.id] = unit;
            }
            // Update the translation unit for the second language.
            else
            {
               UnitIterator i = units.find(unit.id);
               if (i != units.end())
               {
                  i->second.text2 = unit.text;
               }
            }
         }
      }

      result = true;
   }

   return result;
}

bool LuaAnalyzer::writeNewRecordsSqlFile()
{
   const size_t recordsPerQuery = 16;

   std::stringstream aNewRecordsPath;
   aNewRecordsPath << theSourceDir << "new_lua_records.sql";
   std::ofstream ofs1(aNewRecordsPath.str().c_str(), std::ifstream::out | std::ifstream::binary);
   if (ofs1.good())
   {         
      size_t records{};
      UnitIterator u = theUnits.end(); u--;
      for (UnitIterator i = theUnits.begin(); i != theUnits.end(); ++i)
      {
         LuaUnit& unit = i->second;
         Util::replaceAll(unit.text, "'", "\\'");
         Util::replaceAll(unit.text2, "'", "\\'");
         if (records == 0)
         {
            if (theMode == "setup")
            {
               ofs1 << "INSERT INTO `Lua` (`TableId`, `TextId`, `Fr`, `En`) VALUES";
            }
            else
            {
               ofs1 << "INSERT INTO `NewLua` (`TextId`, `Fr`, `En`) VALUES";
            }
         }
         if (theMode == "setup")
         {
            ofs1 << "(0,'";
         }
         else
         {
            ofs1 << "('";
         }
         ofs1 << unit.id << "'," << "'" << unit.text << "'," << "'" << unit.text2 << "')";
         ofs1 << (records == recordsPerQuery - 1 || (i == u) ? ";\n" : ",\n");
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
   
   return true;
}
