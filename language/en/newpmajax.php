<?php
/**
*
* newpmajax [English]
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
	'PMAJAX_GROUP_ALREADY_RECIPIENT'	=> 'Group %s already exists in the recipient list',
	'PMAJAX_NO_SUCH_USER'				=> 'User %s not exists',
	'PMAJAX_TOO_MANY_RECIPIENTS'		=> 'You could not exceed the maximum number (%d) of recipients',
	'PMAJAX_USER_ALREADY_RECIPIENT'		=> 'User %s already exists in the recipient list',
	'PMAJAX_USER_REMOVED_NO_PERMISSION'	=> 'User  %s could not be added, as this user does not have permission to read private messages.',
	'PMAJAX_USER_REMOVED_NO_PM'			=> 'User  %s could not be added, as  this user have disabled private message receipt.',
));
