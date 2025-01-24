<?php

/**
 * ---------------------------------------------------------------------
 * ITSM-NG
 * Copyright (C) 2022 ITSM-NG and contributors.
 *
 * https://www.itsm-ng.org
 *
 * based on GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright (C) 2003-2014 by the INDEPNET Development Team.
 *
 * ---------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of ITSM-NG.
 *
 * ITSM-NG is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * ITSM-NG is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with ITSM-NG. If not, see <http://www.gnu.org/licenses/>.
 * ---------------------------------------------------------------------
 */

namespace Infrastructure\Adapter\Database;

use CommonDBTM;

class QueryObject
{
    public array      $select;
    public CommonDBTM $from;
    public array      $where = [];
    public array      $order = [];
    public array      $join = [];
    public int|null        $limit = null;
    public int|null        $offset = null;

    public function __construct(
        array $select,
        CommonDBTM $from,
        array $where = [],
        array $order = [],
        array $join = [],
        int $limit = null,
        int $offset = null
    ) {
        $this->select = $select;
        $this->from = $from;
        $this->where = $where;
        $this->order = $order;
        $this->join = $join;
        if ($limit !== null) {
            $this->limit = $limit;
        }
        if ($offset !== null) {
            $this->offset = $offset;
        }
    }
}
