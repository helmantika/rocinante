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

#ifndef LANG_ANALYZER_HPP
#define LANG_ANALYZER_HPP

#include <iostream>
#include <fstream>
#include <sstream>
#include <iomanip>
#include <vector>
#include <set>
#include <algorithm>
#include <functional>
#include <map>
#include <netinet/in.h>
#include <regex>
#include "Util.hpp"

/**
 * The lang file record structure.
 */
struct Record
{
   /**
    * The table ID.
    */
   unsigned int tableid;

   /**
    * The sequence ID used to link some tables and strings.
    */
   unsigned int seqid;

   /**
    * The string ID.
    */
   unsigned int stringid;

   /**
    * The file offset in bytes from the end of records.
    */
   unsigned int offset;
};

/**
 * The translation unit data.
 */
struct LangUnit
{
   /**
    * The identifiers and offset.
    */
   Record record;

   /**
    * The text in the first language. It can be empty whether there is another unit that points to 
    * the same text.
    */
   std::string text;

   /**
    * The text in the second language. It can be empty whether there is another unit that points to 
    * the same text.
    */
   std::string text2;

   /**
    * The text in the unofficial language. It can be empty whether it's not translated.
    */
   std::string text3;
   
   /**
    * A hash key is calculated from the three record identifiers: table, string, and sequence.
    */
   unsigned long long key;

   /**
    * The equality operator.
    */
   bool operator==(const LangUnit& unit)
   {
      return record.tableid == unit.record.tableid && 
             record.seqid == unit.record.seqid && 
             record.stringid == unit.record.stringid;
   }
};

/**
 * Generates new_lang_records.tmp, modified_lang_records.tmp, and deleted_lang_records.tmp by means
 * of comparing the old and new versions of two official lang files.
 */
class LangAnalyzer
{
public:
   
   /**
    * Initializes analyzer to build the files at «destdir». Lang files must be located at
    * «sourcedir». The official language codes are given by «baselang» and «extralang» as two letter
    * code (en, fr, or de).
    */
   LangAnalyzer(const std::string& baselang, const std::string& extralang, 
                const std::string& sourcedir, const std::string& mode );

   /**
    * Creates the tmp files.
    */
   Util::Error generate();
   
private:
   
   typedef std::multimap<unsigned int, LangUnit>::iterator UnitIterator;
   typedef std::map<unsigned long long, LangUnit>::iterator OffsetIterator;
   typedef std::set<unsigned int> DataOffset;

   /**
    * Removes junk from basis and assigns second language strings to basis one.
    */
   void filter(std::vector<LangUnit>& basis, std::multimap<unsigned int, LangUnit>& offsets,
               std::map<unsigned long long, LangUnit>& extraLang, DataOffset& dataOffset);

   /**
    * Assigns third language strings to basis one.
    */
   void translate(std::vector<LangUnit>& basis, std::map<unsigned long long, LangUnit>& extraLang);
   
   /**
    * Reads a binary lang file for the first language.
    */
   bool readLangFile(const std::string& file, std::vector<LangUnit>& basis,
                     std::multimap<unsigned int, LangUnit>& offsets, DataOffset& dataOffset);

   /**
    * Reads a binary lang file for the second or third language.
    */
   bool readExtraLangFile(const std::string& file, std::map<unsigned long long, LangUnit>& extraLang);
   
   /**
    * Reads a CSV file that stores translated strings in the target language.
    */
   bool readLangCsvFile(const std::string& file, std::map<unsigned long long, LangUnit>& targetLang);
   
   /**
    * Writes SQL queries in order to insert strings from lang files into a database.
    */
   bool writeNewRecordsSqlFile();
   
   /**
    * Creates a 64-bit hash key for «record».
    */
   unsigned long long createKey(const Record& record);
   
private:
   
   /**
    * The first official language (two letter code).
    */
   std::string theBasisLanguage;

   /**
    * The second official language (two letter code).
    */
   std::string theExtraLanguage;

   /**
    * The directory where files are.
    */
   std::string theSourceDir;
   
   /**
    * The mode: "setup" or "update".
    */
   std::string theMode;

   /**
    * The vector that contains all the records from the first official lang file.
    */
   std::vector<LangUnit> theBasis;

   /**
    * The offsets of the old lang file.
    */
   DataOffset theDataOffsets;
   
   /**
    * The map that associates a file offset with those records of which strings are located in that
    * offset. It is used to avoid duplicates.
    */
   std::multimap<unsigned int, LangUnit> theOffsets;
   
   /**
    * The map that associates a hash key with the second offical language records. The hash key is 
    * calculated from the three record identifiers: table, string, and sequence.
    */
   std::map<unsigned long long, LangUnit> theExtraLang;

   /**
    * The map that associates a hash key with the unoffical language records. The hash key is
    * calculated from the three record identifiers: table, string, and sequence.
    */
   std::map<unsigned long long, LangUnit> theTargetLang;

   /**
    * The translation units.
    */
   std::vector<LangUnit> theUnits;
};

#endif /* LANG_ANALYZER_HPP */

