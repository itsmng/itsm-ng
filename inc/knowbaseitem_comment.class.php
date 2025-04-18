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

/// Class KnowbaseItem_Comment
/// since version 9.2
class KnowbaseItem_Comment extends CommonDBTM
{
    public static function getTypeName($nb = 0)
    {
        return _n('Comment', 'Comments', $nb);
    }

    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if (!$item->canUpdateItem()) {
            return '';
        }

        $nb = 0;
        if ($_SESSION['glpishow_count_on_tabs']) {
            $where = [];
            if ($item->getType() == KnowbaseItem::getType()) {
                $where = [
                   'knowbaseitems_id' => $item->getID(),
                   'language'         => null
                ];
            } else {
                $where = [
                   'knowbaseitems_id' => $item->fields['knowbaseitems_id'],
                   'language'         => $item->fields['language']
                ];
            }

            $nb = countElementsInTable(
                'glpi_knowbaseitems_comments',
                $where
            );
        }
        return self::createTabEntry(self::getTypeName($nb), $nb);
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        self::showForItem($item, $withtemplate);
        return true;
    }

    /**
     * Show linked items of a knowbase item
     *
     * @param $item                     CommonDBTM object
     * @param $withtemplate    integer  withtemplate param (default 0)
    **/
    public static function showForItem(CommonDBTM $item, $withtemplate = 0)
    {
        global $CFG_GLPI;

        // Total Number of comments
        if ($item->getType() == KnowbaseItem::getType()) {
            $where = [
               'knowbaseitems_id' => $item->getID(),
               'language'         => null
            ];
        } else {
            $where = [
               'knowbaseitems_id' => $item->fields['knowbaseitems_id'],
               'language'         => $item->fields['language']
            ];
        }

        $kbitem_id = $where['knowbaseitems_id'];
        $kbitem = new KnowbaseItem();
        $kbitem->getFromDB($kbitem_id);

        $number = countElementsInTable(
            'glpi_knowbaseitems_comments',
            $where
        );

        $cancomment = $kbitem->canComment();
        if ($cancomment) {
            echo "<div class='firstbloc'>";

            $lang = null;
            if ($item->getType() == KnowbaseItemTranslation::getType()) {
                $lang = $item->fields['language'];
            }

            echo self::getCommentForm($kbitem_id, $lang);
            echo "</div>";
        }

        // No comments in database
        if ($number < 1) {
            $no_txt = __('No comments');
            echo "<div class='center'>";
            echo "<table class='tab_cadre_fixe' aria-label='Base item'>";
            echo "<tr><th>$no_txt</th></tr>";
            echo "</table>";
            echo "</div>";
            return;
        }

        // Output events
        echo "<div class='forcomments timeline_history'>";
        echo "<ul class='comments left'>";
        $comments = self::getCommentsForKbItem($where['knowbaseitems_id'], $where['language']);
        $html = self::displayComments($comments, $cancomment);
        echo $html;

        echo "</ul>";
        echo "<script type='text/javascript'>
              $(function() {
                 var _bindForm = function(form) {
                     form.find('input[type=reset]').on('click', function(e) {
                        e.preventDefault();
                        form.remove();
                        $('.displayed_content').show();
                     });
                 };

                 $('.add_answer').on('click', function() {
                    var _this = $(this);
                    var _data = {
                       'kbitem_id': _this.data('kbitem_id'),
                       'answer'   : _this.data('id')
                    };

                    if (_this.data('language') != undefined) {
                       _data.language = _this.data('language');
                    }

                    if (_this.parents('.comment').find('#newcomment' + _this.data('id')).length > 0) {
                       return;
                    }

                    $.ajax({
                       url: '{$CFG_GLPI["root_doc"]}/ajax/getKbComment.php',
                       method: 'post',
                       cache: false,
                       data: _data,
                       success: function(data) {
                          var _form = $('<div class=\"newcomment\" id=\"newcomment'+_this.data('id')+'\">' + data + '</div>');
                          _bindForm(_form);
                          _this.parents('.h_item').after(_form);
                       },
                       error: function() { " .
                            Html::jsAlertCallback(__('Contact your ITSM-NG admin!'), __('Unable to load revision!')) . "
                       }
                    });
                 });

                 $('.edit_item').on('click', function() {
                    var _this = $(this);
                    var _data = {
                       'kbitem_id': _this.data('kbitem_id'),
                       'edit'     : _this.data('id')
                    };

                    if (_this.data('language') != undefined) {
                       _data.language = _this.data('language');
                    }

                    if (_this.parents('.comment').find('#editcomment' + _this.data('id')).length > 0) {
                       return;
                    }

                    $.ajax({
                       url: '{$CFG_GLPI["root_doc"]}/ajax/getKbComment.php',
                       method: 'post',
                       cache: false,
                       data: _data,
                       success: function(data) {
                          var _form = $('<div class=\"editcomment\" id=\"editcomment'+_this.data('id')+'\">' + data + '</div>');
                          _bindForm(_form);
                          _this
                           .parents('.displayed_content').hide()
                           .parent()
                           .append(_form);
                       },
                       error: function() { " .
                            Html::jsAlertCallback(__('Contact your ITSM-NG admin!'), __('Unable to load revision!')) . "
                       }
                    });
                 });


              });
            </script>";

        echo "</div>";
    }

    /**
     * Gat all comments for specified KB entry
     *
     * @param integer $kbitem_id KB entry ID
     * @param string  $lang      Requested language
     * @param integer $parent    Parent ID (defaults to 0)
     *
     * @return array
     */
    public static function getCommentsForKbItem($kbitem_id, $lang, $parent = null)
    {
        $where = [
            'knowbaseitems_id'  => $kbitem_id,
            'language'          => $lang,
            'parent_comment_id' => $parent
         ];
         
         $request = self::getAdapter()->request([
            'FROM'   => 'glpi_knowbaseitems_comments',
            'WHERE'  => $where,
            'ORDERBY' => ['id ASC']
         ]);
         
         $comments = [];
         $results = $request->fetchAllAssociative();
         
         foreach ($results as $db_comment) {
             $db_comment['answers'] = self::getCommentsForKbItem($kbitem_id, $lang, $db_comment['id']);
             $comments[] = $db_comment;
         }
         
         return $comments;
    }

    /**
     * Display comments
     *
     * @param array   $comments   Comments
     * @param boolean $cancomment Whether user can comment or not
     * @param integer $level      Current level, defaults to 0
     *
     * @return string
     */
    public static function displayComments($comments, $cancomment, $level = 0)
    {
        $html = '';
        foreach ($comments as $comment) {
            $user = new User();
            $user->getFromDB($comment['users_id']);

            $html .= "<li class='comment" . ($level > 0 ? ' subcomment' : '') . "' id='kbcomment{$comment['id']}'>";
            $html .= "<div class='h_item left'>";
            if ($level === 0) {
                $html .= '<hr/>';
            }
            $html .= "<div class='h_info'>";
            $html .= "<div class='h_date'>" . Html::convDateTime($comment['date_creation']) . "</div>";
            $html .= "<div class='h_user'>";
            $html .= "<div class='tooltip_picture_border'>";
            $html .= "<img class='user_picture' alt='' src='" .
                   User::getThumbnailURLForPicture($user->fields['picture']) . "'>";
            $html .= "</div>";
            $html .= "<span class='h_user_name'>";
            $userdata = getUserName($user->getID(), 2);
            $html .= $user->getLink() . "&nbsp;";
            $html .= Html::showToolTip(
                $userdata["comment"],
                ['link' => $userdata['link'], 'display' => false]
            );
            $html .= "</span>";
            $html .= "</div>"; // h_user
            $html .= "</div>"; //h_info

            $html .= "<div class='h_content KnowbaseItemComment'>";
            $html .= "<div class='displayed_content'>";

            if ($cancomment) {
                if (Session::getLoginUserID() == $comment['users_id']) {
                    $html .= "<span class='fa fa-pencil-square-o edit_item'
                  data-kbitem_id='{$comment['knowbaseitems_id']}'
                  data-lang='{$comment['language']}'
                  data-id='{$comment['id']}'></span>";
                }
            }

            $html .= "<div class='item_content'>";
            $html .= "<p>{$comment['comment']}</p>";
            $html .= "</div>";
            $html .= "</div>"; // displayed_content

            if ($cancomment) {
                $html .= "<span class='add_answer' title='" . __('Add an answer') . "'
               data-kbitem_id='{$comment['knowbaseitems_id']}'
               data-lang='{$comment['language']}'
               data-id='{$comment['id']}'></span>";
            }

            $html .= "</div>"; //end h_content
            $html .= "</div>";

            if (isset($comment['answers']) && count($comment['answers']) > 0) {
                $html .= "<input type='checkbox' id='toggle_{$comment['id']}'
                             class='toggle_comments' checked='checked'>";
                $html .= "<label for='toggle_{$comment['id']}' class='toggle_label'>&nbsp;</label>";
                $html .= "<ul>";
                $html .= self::displayComments($comment['answers'], $cancomment, $level + 1);
                $html .= "</ul>";
            }

            $html .= "</li>";
        }
        return $html;
    }

    /**
     * Get comment form
     *
     * @param integer       $kbitem_id Knowbase item ID
     * @param string        $lang      Related item language
     * @param false|integer $edit      Comment id to edit, or false
     * @param false|integer $answer    Comment id to answer to, or false
     * @return string
     */
    public static function getCommentForm($kbitem_id, $lang = null, $edit = false, $answer = false)
    {
        $content = '';
        if ($edit !== false) {
            $comment = new KnowbaseItem_Comment();
            $comment->getFromDB($edit);
            $content = $comment->fields['comment'];
        }

        $form = [
           'action' => Toolbox::getItemTypeFormURL(__CLASS__),
           'buttons' => [
              [
                 'type'  => 'submit',
                 'name'  => 'add',
                 'value' => _sx('button', 'Add'),
                 'class' => 'btn btn-secondary',
              ],
              ($edit !== false || $answer !== false) ? [
                 'type'  => 'reset',
                 'name'  => 'cancel',
                 'value' => __('Cancel'),
                 'class' => 'btn btn-secondary',
              ] : [],
              ($edit !== false) ? [
                 'type'  => 'submit',
                 'name'  => 'edit',
                 'value' => _sx('button', 'Edit'),
                 'class' => 'btn btn-secondary',
              ] : [],
           ],
           'content' => [
              ($edit === false ? __('New comment') : __('Edit comment')) => [
                 'visible' => true,
                 'inputs' => [
                    [
                       'type'  => 'hidden',
                       'name'  => 'knowbaseitems_id',
                       'value' => $kbitem_id,
                    ],
                    ($lang !== null) ? [
                       'type'  => 'hidden',
                       'name'  => 'language',
                       'value' => $lang,
                    ] : [],
                    ($answer !== false) ? [
                       'type'  => 'hidden',
                       'name'  => 'parent_comment_id',
                       'value' => $answer,
                    ] : [],
                    ($edit !== false) ? [
                       'type'  => 'hidden',
                       'name'  => 'id',
                       'value' => $edit,
                    ] : [],
                    _n('Comment', 'Comments', 1) => [
                       'type'  => 'textarea',
                       'name'  => 'comment',
                       'value' => $content,
                       'required' => true,
                       'col_lg' => 12,
                       'col_md' => 12,
                    ],
                 ]
              ]
           ]
        ];
        renderTwigForm($form);
    }

    public function prepareInputForAdd($input)
    {
        if (!isset($input["users_id"])) {
            $input["users_id"] = 0;
            if ($uid = Session::getLoginUserID()) {
                $input["users_id"] = $uid;
            }
        }

        return $input;
    }
}
