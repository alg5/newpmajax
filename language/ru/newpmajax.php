<?php
/**
*
* New Private Message With Ajax extension for the phpBB Forum Software package [Russian]
*
* @package newpmajax
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
	'PMAJAX_GROUP_ALREADY_RECIPIENT'	=> 'Группа %s уже присутствует в адресном листе',
	'PMAJAX_NO_SUCH_USER'				=> 'Пользователь %s не зарегистрирован на форуме',
	'PMAJAX_TOO_MANY_RECIPIENTS'		=> 'Вы не можете превысить максимальное число (%d) получателей личного сообщения',
	'PMAJAX_USER_ALREADY_RECIPIENT'		=> 'Пользователь %s уже присутствует в адресном листе',
	'PMAJAX_USER_REMOVED_NO_PERMISSION'	=> 'Пользователь  %s не может быть добавлен, так как у него отсутствуют права на чтение личных сообщений.',
	'PMAJAX_USER_REMOVED_NO_PM'			=> 'Пользователь  %s не может быть добавлен, так как он отключил получение личных сообщений.',
));
