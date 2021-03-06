/*
 * Translation Add-On Generator CGI program for the web app Rocinante.
 * Copyright (C) 2016 Jorge Rodríguez Santos
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
#include <iostream>
#include "LangFileGenerator.hpp"
#include "StrFileGenerator.hpp"

/**
 * This C++ CGI program generates language files for The Elder Scrolls Online.
 *
 * There are three files: two of them are text files called «xx_client.str» and «xx_pregame.str»,
 * where xx is a language code. They contains mainly strings from the user interface. The other file
 * is a binary one. It contains 95% of the total amount of strings. It has an index in the beginning
 * with around 1,000,000 records. After them, there are around 250,000 strings. A string is usually
 * pointed by several records.
 *
 * This program needs four input arguments:
 *    1. sourcedir, a directory where ESO files are.
 *    2. destdir, a directory where add-on will be generated in.
 *    3. baselang, a two letter code that stands for an official ESO language (fr, en, de).
 *    4. extralang, a two letter code that stands for an official ESO language (fr, en, de).
 *    5. targetlang, a two letter code that stands for an unofficial ESO language.
 *
 * This program always returns an errorcode. This way Rocinante (web app) knows what happened.
 *
 * Error Codes
 * -----------
 *  1 - INVALID NUMBER OF ARGUMENTS
 *  2 - INVALID DIRECTORY
 *  3 - INVALID OFFICIAL LANGUAGE
 *  4 - INVALID TARGET LANGUAGE
 *  5 - INVALID TRANSLATION FILE (db_lang.tmp) GENERATED BY ROCINANTE
 *  6 - INVALID LANG FILE
 *  7 - INVALID TRANSLATION FILE (db_lua.tmp) GENERATED BY ROCINANTE
 *  8 - INVALID CLIENT AND/OR PREGAME FILE
 *  9 - CANNOT WRITE FILES
 * 10 - SUCCESS (No error)
 *
 * Even if this program returns no error, it is not granted that generated files are right because
 * this program needs several input files with a strict structure.
 *
 * ¡Be sure this script can access the specified directories and has permission to read the files!
 */
int main(int argc, char* argv[])
{ 
   Util::Error errorcode{Util::Success};

   if (argc != 6)
   {
      errorcode = Util::InvalidNumberOfArguments;
   }
   else
   {
      std::string sourcedir{argv[1]};
      std::string destdir{argv[2]};
      std::string baselang{argv[3]};
      std::string extralang{argv[4]};
      std::string targetlang{argv[5]};

      if (sourcedir != "./esodata/" || destdir != "./esodata/")
      {
         errorcode = Util::InvalidDirectory;
      }
      else if (baselang != "fr" && baselang != "en" && baselang != "de" &&
               extralang != "fr" && extralang != "en" && extralang != "de" &&
               baselang == extralang)
      {
         errorcode = Util::InvalidOfficialLanguage;
      }
      else if (targetlang.size() != 2)
      {
         errorcode = Util::InvalidTargetLanguage;
      }
      else
      {
         {
            LangFileGenerator lang(baselang, extralang, sourcedir, destdir);
            errorcode = lang.generate(targetlang);
         }

         if (errorcode == Util::Success)
         {
            StrFileGenerator str(baselang, extralang, sourcedir, destdir);
            errorcode = str.generate(targetlang);
         }
      }
   }

   std::cout << +errorcode << std::endl;
   return errorcode;
}
