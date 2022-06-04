/*
 * Translation Add-On Generator CGI program for the web app "Rocinante".
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

#ifndef UTIL_HPP
#define UTIL_HPP

#include <string>

namespace Util
{
   /**
    * The error codes.
    */
   enum Error
   {
      InvalidNumberOfArguments = 1,
      InvalidDirectory = 2,
      InvalidOfficialLanguage = 3,
      InvalidTargetLanguage = 4,
      Invalid_db_lang_tmp_File = 5,
      InvalidLangFile = 6,
      Invalid_db_lua_tmp_File = 7,
      InvalidClientOrPregameFile = 8,
      CannotWriteFiles = 9,
      Success = 10
   };

   /**
    * Replace all occurrences of the search string in the input with the substitute string. 
    * The input sequence is modified in-place.
    */
   void replaceAll(std::string& search, const std::string& input, const std::string& substitute);
}

#endif

