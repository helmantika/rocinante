#include "StrFileGenerator.hpp"

StrFileGenerator::StrFileGenerator(const std::string& language, const std::string& extralang,
                                   const std::string& sourcedir, const std::string& destdir)
: theOfficialLanguage{language},
  theExtraLanguage{extralang},
  theSourceDir{sourcedir},
  theDestDir{destdir}
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

Util::Error StrFileGenerator::generate(const std::string& language)
{
   theTargetLanguage = language;

   // Read official "client" LUA file.
   if (!readLuaFile(theSourceDir + theOfficialLanguage + "_client.lua", theClient))
   {
      return Util::InvalidClientOrPregameFile;
   }
   // Read official "pregame" LUA file.
   if (!readLuaFile(theSourceDir + theOfficialLanguage + "_pregame.lua", thePregame))
   {
      return Util::InvalidClientOrPregameFile;
   }

   // Read another official "client" LUA file.
   if (!readLuaFile(theSourceDir + theExtraLanguage + "_client.lua", theExtraLang))
   {
      return Util::InvalidClientOrPregameFile;
   }
   // Read another official "pregame" LUA file.
   if (!readLuaFile(theSourceDir + theExtraLanguage + "_pregame.lua", theExtraLang))
   {
      return Util::InvalidClientOrPregameFile;
   }

   // Read translation.
   if (!readTranslationFile(theDestDir + "db_lua.tmp"))
   {
      return Util::Invalid_db_lua_tmp_File;
   }

   // Assign second language strings to pregame records.
   std::size_t pregameSize = thePregame.size();
   for (LuaUnit* i = &thePregame[0]; i < &thePregame[pregameSize]; ++i)
   {
      UnitIterator u = theExtraLang.find(i->id);
      if (u != theExtraLang.end())
      {
         i->text = u->second.text;
      }
   }

   // Assign translated strings to client records.
   std::size_t clientSize = theClient.size();
   for (LuaUnit* i = &theClient[0]; i < &theClient[clientSize]; ++i)
   {
      UnitIterator u = theExtraLang.find(i->id);
      if (u != theExtraLang.end())
      {
         i->text = u->second.text;
      }
   }

   // Assign translated strings to pregame records.
   for (LuaUnit* i = &thePregame[0]; i < &thePregame[pregameSize]; ++i)
   {
      UnitIterator u = theTranslation.find(i->id);
      if (u != theTranslation.end())
      {
         i->text = u->second.text;
      }
   }

   // Assign translated strings to client records.
   for (LuaUnit* i = &theClient[0]; i < &theClient[clientSize]; ++i)
   {
      UnitIterator u = theTranslation.find(i->id);
      if (u != theTranslation.end())
      {
         i->text = u->second.text;
      }
   }

   // Create «xx_pregame.str» file.
   std::ofstream ofs1(theDestDir + theTargetLanguage + "_pregame.str", std::ifstream::out | std::ifstream::binary);
   if (ofs1.good())
   {
      // Add header.
      processStrHeaderFile(theSourceDir + "pregame_str.txt", ofs1);

      // Add string translation.
      for (LuaUnit* i = &thePregame[0]; i < &thePregame[pregameSize]; ++i)
      {
         ofs1 << "[" << i->id << "] = " << "\"" << i->text << "\"\r\n";
      }
   }
   else
   {
      return Util::CannotWriteFiles;
   }

   // Create «xx_client.str» file.
   std::ofstream ofs2(theDestDir + theTargetLanguage + "_client.str", std::ifstream::out | std::ifstream::binary);
   if (ofs2.good())
   {
      // Add header.
      processStrHeaderFile(theSourceDir + "client_str.txt", ofs2);

      // Add string translation.
      for (LuaUnit* i = &theClient[0]; i < &theClient[clientSize]; ++i)
      {
         ofs2 << "[" << i->id << "] = " << "\"" << i->text << "\"\r\n";
      }
   }
   else
   {
      return Util::CannotWriteFiles;
   }

   return Util::Success;
}

bool StrFileGenerator::readLuaFile(const std::string& file, std::vector<LuaUnit>& records)
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
      for (std::size_t i = 0; i < texts.size(); ++i)
      {
         std::string& str = texts[i];
         if (str.find("SafeAddString") != std::string::npos)
         {
            LuaUnit unit;

            // Remove "SafeAddString".
            str.erase(0, 13);

            // Remove first parenthesis.
            std::size_t pos = str.find_first_of("(");
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
            std::size_t pos1 = str.find_first_of("\"");
            std::size_t pos2 = str.find_last_of("\"");
            unit.text = str.substr(pos1 + 1, pos2 - pos1 - 1);
            // Replace "\n" with line feed character.
            Util::replaceAll(unit.text, "\\n", "\n");

            // Find and store version number of the string.
            pos = str.find_last_of(",");
            std::string temp = str.substr(pos, str.size() - pos);
            std::string temp2;
            for (std::size_t n = 0; n < temp.size(); ++n)
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

            // Store the translation unit.
            records.push_back(unit);
         }
      }

      result = true;
   }

   return result;
}

bool StrFileGenerator::readLuaFile(const std::string& file, std::map<std::string, LuaUnit>& records)
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
      for (std::size_t i = 0; i < texts.size(); ++i)
      {
         std::string& str = texts[i];
         if (str.find("SafeAddString") != std::string::npos)
         {
            LuaUnit unit;

            // Remove "SafeAddString".
            str.erase(0, 13);

            // Remove first parenthesis.
            std::size_t pos = str.find_first_of("(");
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
            std::size_t pos1 = str.find_first_of("\"");
            std::size_t pos2 = str.find_last_of("\"");
            unit.text = str.substr(pos1 + 1, pos2 - pos1 - 1);
            // Replace "\n" with line feed character.
            Util::replaceAll(unit.text, "\\n", "\n");

            // Find and store version number of the string.
            pos = str.find_last_of(",");
            std::string temp = str.substr(pos, str.size() - pos);
            std::string temp2;
            for (std::size_t n = 0; n < temp.size(); ++n)
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

            // Store the translation unit.
            records[unit.id] = unit;
         }
      }

      result = true;
   }

   return result;
}

bool StrFileGenerator::readTranslationFile(const std::string& file)
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
         ifs.read(&c, sizeof ( char));
         if (c != 0 && c != 0xd)
         {
            contents.push_back(c);
         }
      }
      while (!ifs.eof());

      ifs.close();

      // Process contents until every string is read and stored.
      while (!contents.empty())
      {
         // Find two identifiers.
         std::size_t i1 = contents.find("{{");
         std::size_t i2 = contents.find("}}");
         std::string id = contents.substr(i1, i2 - i1 + 2); // +2 because of "}}" length.
         // Remove curly brackets.
         id = id.substr(2, id.size() - 4);
         i1 = id.find(",");
         std::string idStr = id.substr(0, i1);
         std::string versionStr = id.substr(i1 + 1, id.size() - i1);

         // Store data in a register.
         LuaUnit unit;
         std::stringstream ss1(idStr);
         ss1 >> unit.id;
         std::stringstream ss2(versionStr);
         ss2 >> unit.version;

         // Drop processed strings and read translated text.
         std::string endid("}}");
         std::size_t t = contents.find(endid);
         t += endid.size();
         contents = contents.substr(t, contents.size() - t + 1);
         std::size_t i3 = contents.find("{{");
         std::string text = (i3 != std::string::npos ? contents.substr(0, i3) : contents);
         if (i3 != std::string::npos)
         {
            contents = contents.substr(i3, contents.size() - i3 + 1);
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
         // Replace line feed character with '\n'.
         Util::replaceAll(text, "\n", "\\n");
         
         unit.text = text;

         // Patch text for Spanish and Portuguese language.
         if (theTargetLanguage == "es" || theTargetLanguage == "br" || theTargetLanguage == "pt")
         {
            Util::replaceAll(unit.text, "a <<", "æ <<");
            Util::replaceAll(unit.text, "a |c", "æ |c");
         }
         
         // Store translation unit.
         theTranslation[unit.id] = unit;
      }

      result = true;
   }

   return result;
}

void StrFileGenerator::processStrHeaderFile(const std::string& file, std::ofstream& ofs)
{
   // Read file content.
   std::ifstream ifs(file.c_str(), std::ifstream::in | std::ifstream::binary);
   if (ifs.good())
   {
      char c;
      std::string contents;

      do
      {
         ifs.read(&c, sizeof(char));
         if (c != 0)
         {
            contents.push_back(c);
         }
      }
      while (!ifs.eof());
      ifs.close();

      ofs << contents;
   }
}
