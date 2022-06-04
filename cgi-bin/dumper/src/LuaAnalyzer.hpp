/*
 * Database Updater CGI program for the web app "Rocinante".
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

#ifndef LUA_ANALYZER_HPP
#define LUA_ANALYZER_HPP

#include <string>
#include <vector>
#include <map>
#include <iostream>
#include <fstream>
#include <sstream>
#include "Util.hpp"

/**
 * The translation unit data.
 */
struct LuaUnit
{
   /**
    * The identifier.
    */
   std::string id;

   /**
    * The text to be translated.
    */
   std::string text;

   /**
    * The text given in a second official language.
    */
   std::string text2;

   /**
    * The text version.
    */
   int version;
};

/**
 * Generates new_lua_records.tmp, modified_lua_records.tmp, and deleted_lua_records.tmp by means
 * of comparing the old and new versions of two official lang files.
 */
class LuaAnalyzer
{
public:

   /**
    * Initializes analyzer to build the files at «destdir». Lua files must be located at
    * «sourcedir». The official language codes are given by «baselang» and «extralang» as two letter
    * code (en, fr, or de).
    */
   LuaAnalyzer(const std::string& baselang, const std::string& extralang,
               const std::string& sourcedir, const std::string& mode);

   /**
    * Creates the tmp files.
    */
   Util::Error generate();

private:

   /**
    * Reads a LUA text file and store all the records in «records».
    */
   bool readLuaFile( const std::string& file, std::map<std::string, LuaUnit>& units, 
                     const std::string& language, bool isSecondLanguage = false);
   
   /**
    * Writes SQL queries in order to insert strings from LUA files into a database.
    */
   bool writeNewRecordsSqlFile();
   
private:

   typedef std::map<std::string, LuaUnit>::iterator UnitIterator;

   /**
    * The first official language (two letter code).
    */
   std::string theBasisLanguage;

   /**
    * The second official language (two letter code).
    */
   std::string theExtraLanguage;

   /**
    * The directory where old version files are.
    */
   std::string theSourceDir;
   
   /**
    * The mode: "setup" or "update".
    */
   std::string theMode;

   /**
    * The vector that contains all the records from an official «xx_client.lua» file for the new version.
    */
   std::vector<LuaUnit> theClient;

   /**
    * The vector that contains all the records from an official «xx_pregame.lua» file for the new version.
    */
   std::vector<LuaUnit> thePregame;

   /**
    * The map that associates a record ID with the new version units.
    */
   std::map<std::string, LuaUnit> theUnits;
};

#endif /* LUA_ANALYZER_HPP */
