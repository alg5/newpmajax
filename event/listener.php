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
	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var string phpbb_root_path */
	protected $phpbb_root_path;

	/** @var \phpbb\controller\helper */
	protected $controller_helper;

		/**
	* Constructor
	* @param \phpbb\template\template	$template	Template object
	* @param \phpbb\user						$user				User object
	* @param string								$phpbb_root_path	phpbb_root_path
	* @param \phpbb\controller\helper		$controller_helper	Controller helper object
	* @param array								$return_error		array

	* @access public
	*/

	public function __construct(\phpbb\template\template $template, \phpbb\user $user, $phpbb_root_path, \phpbb\controller\helper $controller_helper)
	{
		$this->template = $template;
		$this->user = $user;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->controller_helper = $controller_helper;
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
			'U_NEWPMAJAX_PATH_ADD_TO'		=>  $this->controller_helper->route('alg_newpmajax_controller', array('action' => 'add_to')),
			'U_NEWPMAJAX_PATH_ADD_BCC'		=>  $this->controller_helper->route('alg_newpmajax_controller', array('action' => 'add_bcc')),
			));
	}
}
