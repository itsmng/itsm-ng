<?php

/**
 * ---------------------------------------------------------------------
 * GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright (C) 2015-2022 Teclib' and contributors.
 *
 * http://glpi-project.org
 *
 * based on GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright (C) 2003-2014 by the INDEPNET Development Team.
 *
 * ---------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of GLPI.
 *
 * GLPI is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GLPI is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GLPI. If not, see <http://www.gnu.org/licenses/>.
 * ---------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}


/**
 * Only an HTMLTableMain can create an HTMLTableSuperHeader.
 * @since 0.84
**/
class HTMLTableSuperHeader extends HTMLTableHeader
{
    /// The headers of each column
    private $headerSets = [];
    /// The table that owns the current super header
    private $table;


    /**
     * @param HTMLTableMain        $table    HTMLTableMain object: table owning the current header
     * @param string               $name     the name of the header
     * @param string               $content  see inc/HTMLTableEntity#__construct()
     * @param HTMLTableSuperHeader $father   HTMLTableSuperHeader objet (default NULL)
    **/
    public function __construct(HTMLTableMain $table, $name, $content, HTMLTableSuperHeader $father = null)
    {

        $this->table = $table;
        parent::__construct($name, $content, $father);
    }


    /**
     * Compute the Least Common Multiple of two integers
     *
     * @param $first
     * @param $second
     *
     * @return integer LCM of $first and $second
    **/
    private static function LCM($first, $second)
    {

        $result = $first * $second;
        while ($first > 1) {
            $reste = $first % $second;
            if ($reste == 0) {
                $result /= $second;
                break;  // leave when LCM is found
            }
            $first = $second;
            $second = $reste;
        }
        return $result;
    }


    public function isSuperHeader()
    {
        return true;
    }


    /**
     * @see HTMLTableHeader::getHeaderAndSubHeaderName()
    **/
    public function getHeaderAndSubHeaderName(&$header_name, &$subheader_name)
    {

        $header_name    = $this->getName();
        $subheader_name = '';
    }


    public function getCompositeName()
    {
        return $this->getName() . ':';
    }


    protected function getTable()
    {
        return $this->table;
    }


    /**
     * compute the total number of current super header colspan: it is the Least Common
     * Multiple of the colspan of each subHeader it owns.
     *
     * @param integer $number the colspan for this header given by the group
    **/
    public function updateNumberOfSubHeader($number)
    {
        $this->setColSpan(self::LCM($number, $this->getColSpan()));
    }


    /**
     * The super headers always have to be displayed, conversely to sub headers
     *
     * @return true
    **/
    public function hasToDisplay()
    {
        return true;
    }
}
