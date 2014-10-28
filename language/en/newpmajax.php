<?php
/** 
*
* liveSearch [English]
*
* @package liveSearch
* @copyright (c) 2014 alg
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine

$lang = array_merge($lang, array(
	'INCORRECT_SEARCH'			=> 'Некорректные параметры запрошенного действия',
	'LIVE_SEARCH_CAPTION'		=> 'Быстрый поиск',
	'LIVE_SEARCH_FORUM'		=> 'Форумы',
	'LIVE_SEARCH_FORUM_TXT'		=> 'Название форума...',
	'LIVE_SEARCH_FORUM_T'			=> 'Для быстрого поиска начните набирать название форума/категории',
	'LIVE_SEARCH_GO_PROFILE'				=> 'Перейти в профиль',
	'LIVE_SEARCH_POSTS_BY_USER'		=> 'Сообщения пользователя',
	'LIVE_SEARCH_TOOLTIP_ALL'		=> 'Поиск во всех форумах конференции',
	'LIVE_SEARCH_TOOLTIP_BY_FORUM'		=> 'Поиск в форуме ',
	'LIVE_SEARCH_TOOLTIP_BY_TOPIC'		=> 'Поиск в теме ',
	'LIVE_SEARCH_TOPIC'		=> 'Темы',
	'LIVE_SEARCH_TOPICS_BY_USER'		=> 'Темы пользователя',
	'LIVE_SEARCH_TXT'		=> 'Название темы...',
	'LIVE_SEARCH_T'			=> 'Для быстрого поиска начните набирать название темы',
	'LIVE_SEARCH_USER'		=> 'Пользователи',
	'LIVESEARCH_USER_TXT'	=> 'Имя...',
	'LIVESEARCH_USER_T'	=> 'Для быстрого поиска начинайте печатать имя пользователя',
	'LIVESEARCH_USERTOPIC_RESULT'	=> 'Темы пользователя  %1$s',
	'LIVESEARCH_USERTOPIC_RESULT_IN_FORUM'	=> 'Темы пользователя  %1$s в форуме  %2$s',
	'LIVESEARCH_USERTOPIC_RESULT_IN_SUBFORUMS'	=> ' и его подфорумах',
	'LIVE_SEARCH_EYE_BUTTON_OPEN_T'	=> 'Показать панель поиска',
	'LIVE_SEARCH_EYE_BUTTON_CLOSE_T'	=> 'Скрыть панель поиска',
));
