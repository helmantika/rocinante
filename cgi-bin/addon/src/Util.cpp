#include "Util.hpp"

namespace Util
{
   void replaceAll(std::string& search, const std::string& input, const std::string& substitute)
   {
      if (!input.empty())
      {
         size_t start_pos = 0;
         while ((start_pos = search.find(input, start_pos)) != std::string::npos)
         {
            search.replace(start_pos, input.length(), substitute);
            start_pos += substitute.length();
         }
      }
   }
}
