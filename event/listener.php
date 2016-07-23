<?php
/**
 *
 * @package newpmajax
 * @copyright (c) 2014 alg
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace alg\newpmajax\event;

/**
* Event listener
*/
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class listener implements EventSubscriberInterface
{

	public function __construct(\phpbb\template\template $template, \phpbb\user $user, $phpbb_root_path)
	{
		$this->template = $template;
		$this->user = $user;
		$this->phpbb_root_path = $phpbb_root_path;
	}

	static public function getSubscribedEvents()
	{
		return array(
			'core.page_header_after'			=> 'page_header_after',
		);
	}

	public function page_header_after($event)
	{
		$this->user->add_lang_ext('alg/newpmajax', 'newpmajax');
		$this->template->assign_vars(array(
			'U_NEWPMAJAX_PATH'				  => append_sid("{$this->phpbb_root_path}newpmajax/"),
			));
	}
}
