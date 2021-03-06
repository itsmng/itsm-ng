#!/usr/bin/perl
#!/usr/bin/perl -w 
# /**
#  * ---------------------------------------------------------------------
#  * GLPI - Gestionnaire Libre de Parc Informatique
#  * Copyright (C) 2015-2022 Teclib' and contributors.
#  *
#  * http://glpi-project.org
#  *
#  * based on GLPI - Gestionnaire Libre de Parc Informatique
#  * Copyright (C) 2003-2014 by the INDEPNET Development Team.
#  *
#  * ---------------------------------------------------------------------
#  *
#  * LICENSE
#  *
#  * This file is part of GLPI.
#  *
#  * GLPI is free software; you can redistribute it and/or modify
#  * it under the terms of the GNU General Public License as published by
#  * the Free Software Foundation; either version 2 of the License, or
#  * (at your option) any later version.
#  *
#  * GLPI is distributed in the hope that it will be useful,
#  * but WITHOUT ANY WARRANTY; without even the implied warranty of
#  * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#  * GNU General Public License for more details.
#  *
#  * You should have received a copy of the GNU General Public License
#  * along with GLPI. If not, see <http://www.gnu.org/licenses/>.
#  * ---------------------------------------------------------------------
# */

use File::Basename;

if (@ARGV != 0) {
   print "USAGE update_mo.pl\n\n";
   exit();
}

my $dirname = dirname(__FILE__) . '/../../locales';
opendir(DIRHANDLE, $dirname) || die "ERROR: can not read locales directory\n";
foreach (readdir(DIRHANDLE)) {
   if ($_ ne '..' && $_ ne '.') {
      if (!(-l "$dirname/$_")) {
         if (index($_,".po",0) == length($_)-3) {
            $lang=$_;
            $lang=~s/\.po//;
            `msgfmt $dirname/$_ -o $dirname/$lang.mo`;
         }
      }
   }
}
closedir DIRHANDLE; 
