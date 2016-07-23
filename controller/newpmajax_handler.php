<?php
/**
*
* @author Alg
* @version 1.0.0	$
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

namespace alg\newpmajax\controller;

class newpmajax_handler
{
	protected $thankers = array();
	public function __construct(\phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\auth\auth $auth, \phpbb\template\template $template, \phpbb\user $user, \phpbb\cache\driver\driver_interface $cache, $phpbb_root_path, $php_ext, \phpbb\request\request_interface $request, $table_prefix, $phpbb_container, \phpbb\pagination $pagination)
	{
		$this->config = $config;
		$this->db = $db;
		$this->auth = $auth;
		$this->template = $template;
		$this->user = $user;
		$this->cache = $cache;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
		$this->request = $request;
		$this->phpbb_container = $phpbb_container;
		$this->pagination =  $pagination;
		$this->message = ''; // save warning in here
		$this->return = array(); // save returned data in here
		$this->error = array(); // save errors in here

	}

	public function main($action)
	{
		$this->user->add_lang_ext('alg/newpmajax', 'newpmajax');
		//not allowed send PM for anonymous
		if ($this->user->data['is_bot'] || $this->user->data['user_id'] == ANONYMOUS  )
		{
			$return_error['ERROR'][] = $this->user->lang['LOGIN_REQUIRED'];
			$json_response = new \phpbb\json_response;
			$json_response->send($return_error);
		}
		switch ($action)
		{
			case 'add_to':
			case 'add_bcc':
				$this->add_sender($action);
			break;

			default:
				$this->error[] = array('error' => $this->user->lang('NO_ACTION_MODE', E_USER_ERROR));

		}
		if (sizeof($this->error))
		{
			$return_error = array();
			foreach ($this->error as $cur_error)
			{
					// replace lang vars if possible
					$return_error['ERROR'][] = (isset($this->user->lang[$cur_error['error']])) ? $this->user->lang[$cur_error['error']] : $cur_error['error'];
			}
			$json_response = new \phpbb\json_response;
			$json_response->send($return_error);
		}
		else
		{
			$json_response = new \phpbb\json_response;
			$json_response->send($this->return);
		}
	}
	private function add_sender($action)
	{
		include_once($this->phpbb_root_path . 'includes/ucp/ucp_pm_compose.' . $this->php_ext);
		include_once($this->phpbb_root_path . 'includes/functions_user.' . $this->php_ext);
		$this->user->add_lang(array( 'viewtopic'));
		$this->user->add_lang(array( 'ucp'));

		if (!$this->auth->acl_get('u_sendpm'))
		{
			$this->error[] = array('error' => $this->user->lang['NO_AUTH_SEND_MESSAGE']);
			return;
		}

		add_form_key('ucp_pm_compose');
		// Grab only parameters needed here
		$this->address_list	= $this->request->variable('address_list', array('' => array(0 => '')));    //already exist recipients don't need to check permission
		$this->user_list = array();

		$usernames =  array();
		$username_list = $this->request->variable('username_list', '', true);
		$message = '';
		#region AddUsers
		if ($username_list)
		{
			$usernames =  array_unique(explode("\n", $username_list));
			if (sizeof($usernames))
			{
				$user_id_ary = array();
				user_get_id_name($user_id_ary, $usernames, array(USER_NORMAL, USER_FOUNDER, USER_INACTIVE));
				$this->user_list = $this->get_user_list($usernames);
				if (sizeof($this->user_list) < sizeof($usernames) )
				{
					//find non-existing users
					foreach ($usernames as $username)
					{
						if (!$this->is_user_exists($username, $this->user_list))
						{
							$message .=  sprintf($this->user->lang['PMAJAX_NO_SUCH_USER'] . '<br />', $username);
						}
					}
				}
				if (sizeof($this->user_list))
				{
					// Now, make sure that new users not exist in address_list ;)
					foreach ($this->user_list as $key => $user)
					{
						if (isset($this->address_list['u'][$user['user_id']]))
						{
							//user already recipient (don't need add and check it)
							$message .=  sprintf($this->user->lang['PMAJAX_USER_ALREADY_RECIPIENT'] . '<br />', $user['username']);
							$user_id_ary = array_diff($user_id_ary, array($user['user_id']));   //remove ids of duplicate recipients
							unset($this->user_list[$key]);
						}
					}
					if (sizeof($user_id_ary))
					{
						// Check for disallowed recipients
							$can_ignore_allow_pm = $this->auth->acl_gets('a_', 'm_') || $this->auth->acl_getf_global('m_');
							// Administrator deactivated users check and we need to check their
							//		PM status (do they want to receive PM's?)
							// 		Only check PM status if not a moderator or admin, since they
							//		are allowed to override this user setting
							$sql = 'SELECT user_id, username, user_allow_pm
									FROM ' . USERS_TABLE . '
									WHERE ' . $this->db->sql_in_set('user_id', $user_id_ary) . '
									AND (
											(user_type = ' . USER_INACTIVE . '
											AND user_inactive_reason = ' . INACTIVE_MANUAL . ')
											' . ($can_ignore_allow_pm ? '' : ' OR user_allow_pm = 0') . '
										)';
							$result = $this->db->sql_query($sql);
							$removed_no_pm = $removed_no_permission = false;
							while ($row = $this->db->sql_fetchrow($result))
							{
								if (!$can_ignore_allow_pm && !$row['user_allow_pm'])
								{
									$username = $this->remove_user_from_user_list($row['user_id']);
									$message .=  sprintf($this->user->lang['PMAJAX_USER_REMOVED_NO_PM'] . '<br />', $username);

								}
								else
								{
									$username = $this->remove_user_from_user_list($row['user_id']);
									$message .=  sprintf($this->user->lang['PMAJAX_USER_REMOVED_NO_PERMISSION'] . '<br />', $username);
								}
								$user_id_ary = array_diff($user_id_ary, array($row['user_id']));   //remove id for this user
							}
							$this->db->sql_freeresult($result);
					}//sizeof($user_id_ary))
					if ( sizeof($user_id_ary))
					{
						// Check if users have permission to read PMs
						$can_read = $this->auth->acl_get_list($user_id_ary, 'u_readpm');
						$can_read = (empty($can_read) || !isset($can_read[0]['u_readpm'])) ? array() : $can_read[0]['u_readpm'];
						$cannot_read_list = array_diff($user_id_ary, $can_read);
						if (!empty($cannot_read_list))
						{
							foreach ($cannot_read_list as $cannot_read)
							{
								$username = $this->remove_user_from_user_list($cannot_read);
								$user_id_ary = array_diff($user_id_ary, $cannot_read);   //remove id for this user
								$message .=  sprintf($this->user->lang['PMAJAX_USER_REMOVED_NO_PERMISSION'] . '<br />', $username);
							}
						}
					}
					if ( sizeof($user_id_ary))
					{
						// Check if users are banned
						$banned_user_list = phpbb_get_banned_user_ids($user_id_ary, false);
						if (!empty($banned_user_list))
						{
							foreach ($banned_user_list as $banned_user)
							{
								$username = $this->remove_user_from_user_list($banned_user);
								$user_id_ary = array_diff($user_id_ary, $cannot_read);   //remove id for this user
								$message .=  sprintf($this->user->lang['PMAJAX_USER_REMOVED_NO_PERMISSION'] . '<br />', $username);
							}
						}
					}
				}//sizeof($this->user_list
			}
		}

		#endregion

		$group_list = $this->request->variable('group_list', array(0));
		$this->group_list =  array();
		#region AddGroups

		// Check mass pm to group permission
		if (sizeof($group_list)  && (!$this->config['allow_mass_pm'] || !$this->auth->acl_get('u_masspm_group')))
		{
			$message .=  $this->user->lang['NO_AUTH_GROUP_MESSAGE'] . '<br />';
			$group_list = array();
		}
		if (sizeof($group_list))
		{
			$sql = 'SELECT g.group_id AS id, g.group_name AS name, g.group_colour AS colour, g.group_type
				FROM ' . GROUPS_TABLE . ' g';

			if (!$this->auth->acl_gets('a_group', 'a_groupadd', 'a_groupdel'))
			{
				$sql .= ' LEFT JOIN ' . USER_GROUP_TABLE . ' ug
					ON (
						g.group_id = ug.group_id
						AND ug.user_id = ' . $user->data['user_id'] . '
						AND ug.user_pending = 0
					)
					WHERE (g.group_type <> ' . GROUP_HIDDEN . ' OR ug.user_id = ' . $this->user->data['user_id'] . ')';
			}

			$sql .= ($this->auth->acl_gets('a_group', 'a_groupadd', 'a_groupdel')) ? ' WHERE ' : ' AND ';

			$sql .= 'g.group_receive_pm = 1
				AND ' . $this->db->sql_in_set('g.group_id', $group_list) . '
				ORDER BY g.group_name ASC';

			$result = $this->db->sql_query($sql);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$row['name'] = ($row['group_type'] == GROUP_SPECIAL) ? $this->user->lang['G_' . $row['name']] : $row['name'];
				// Now, make sure that group not exist in address_list
				if (isset($this->address_list['g'][$row['id']]))
				{
					$message .=  sprintf($this->user->lang['PMAJAX_GROUP_ALREADY_RECIPIENT'] . '<br />', $row['name']);
				}
				else
				{
					$this->group_list[] = $row;
				}
			}
				$this->db->sql_freeresult($result);
		}

		#endregion

		#region Handle num recipients
		$num_recipients = sizeof($this->user_list);

		$pm_action	= $this->request->variable('action', '');
		$reply_to_all	= $this->request->variable('reply_to_all', 0);

		if (sizeof($this->user_list) + sizeof($this->group_list))
		{
			// Get maximum number of allowed recipients
			$sql = 'SELECT MAX(g.group_max_recipients) as max_recipients
				FROM ' . GROUPS_TABLE . ' g, ' . USER_GROUP_TABLE . ' ug
				WHERE ug.user_id = ' . $this->user->data['user_id'] . '
					AND ug.user_pending = 0
					AND ug.group_id = g.group_id';
			$result = $this->db->sql_query($sql);
			$max_recipients = (int) $this->db->sql_fetchfield('max_recipients');
			$this->db->sql_freeresult($result);
			$max_recipients = (!$max_recipients) ? $this->config['pm_max_recipients'] : $max_recipients;

			// If this is a quote/reply "to all"... we may increase the max_recpients to the number of original recipients
			if (($pm_action == 'reply' || $pm_action == 'quote') && $max_recipients && $reply_to_all)
			{
				$max_recipients = ($max_recipients < sizeof($this->address_list['u'])) ? sizeof($this->address_list['u']) : $max_recipients;
			}

			// Check for too many recipients
			$num_recipients_exist = !empty($this->address_list['u']) ? sizeof($this->address_list['u']) : 0;
			if ( $max_recipients && $num_recipients_exist + $num_recipients > $max_recipients)
			{
				$this->error[] = array('error' => $this->user->lang('PMAJAX_TOO_MANY_RECIPIENTS', $max_recipients));
				return;
			}
			// Check mass pm to users permission
			if ((!$this->config['allow_mass_pm'] || !$this->auth->acl_get('u_masspm')) && $num_recipients + $num_recipients_exist > 1)
			{
				$this->error[] = array('error' => $this->user->lang('PMAJAX_TOO_MANY_RECIPIENTS', $max_recipients));
				return;
			}
		}
		#endregion

		$add_to = $action == "add_to" ? true : false;
		$add_bcc	=  $action == "add_bcc" ? true : false;

		$type = ($add_to) ? 'to' : 'bcc';
		//build output
		$recipient_u = array();
		$recipient_g = array();
		foreach ($this->user_list as $user)
		{
			$view_path = get_username_string('profile', $user['user_id'], $user['username'], $user['colour']);
			$view_path = str_replace('../', '', $view_path);
			$name_full = get_username_string('full', $user['user_id'], $user['username'], $user['colour']);
			$name_full = str_replace('../', '', $name_full);
			$row = array(
				'UG_ID'		=> $user['user_id'],
				'NAME'		=> $user['username'],
				'COLOUR'	=> $user['colour'] ? '#' . $user['colour'] : '',
				'NAME_FULL'		=> $name_full,
			);
			$recipient_u[] = $row;
		}
		$recipient_g = array();
		foreach ($this->group_list as $group)
		{
			$view_path = append_sid("{$this->phpbb_root_path}memberlist.$this->php_ext", 'mode=group&amp;g=' . $group['id']);
			$view_path = str_replace('../', '', $view_path);
			$row = array(
				'UG_ID'		=> $group['id'],
				'NAME'		=> $group['name'],
				'COLOUR'	=> $group['colour'] ? '#' . $group['colour'] : '#0000FF',
				'U_VIEW'		=>  $view_path,
			);
			$recipient_g[] = $row;
		}
		$this->return = array(
			'RECIPIENT_U_LIST'		=> $recipient_u ,
			'RECIPIENT_G_LIST'		=> $recipient_g ,
			'NUM_RECIPIENTS'		=> sizeof($recipient_u) + sizeof($recipient_g),
			'MESSAGE'		=> $message,
		);
	}
	private function is_item_exists($item, $ids_ary)
	{
		foreach ($ids_ary as $id)
		{
			if ($id == $item)
			{
				return true;
			}
		}
		return false;
	}
	private function is_user_exists($username, $user_info)
	{
		foreach ($user_info as $user)
		{
			if ($user['username'] == $username)
			{
				return $user;
			}
		}
		return false;
	}
	private function get_user_list($username_ary, $user_type = false)
	{
		$which_ary =  'username_ary';

		if ($$which_ary && !is_array($$which_ary))
		{
			$$which_ary = array($$which_ary);
		}

		$sql_in = array_map('utf8_clean_string', $$which_ary);
		// Grab the user id/username records
		$sql_where = 'username_clean';
		$sql = 'SELECT user_id, username, user_type, user_allow_pm, user_colour as colour 
			FROM ' . USERS_TABLE . '
			WHERE ' . $this->db->sql_in_set($sql_where, $sql_in) .
			' ORDER BY username_clean ASC';
		$result = $this->db->sql_query($sql);
		$user_info = array();
		$user_count = 0;

		while ($row = $this->db->sql_fetchrow($result))
		{
			$user_info[] = $row;
			$user_count ++;
		}
		$this->db->sql_freeresult($result);
		// print_r($this->user_list);
		return $user_info;

		foreach ($username_ary as $username)
		{
			$user = $this->is_user_exists($username, $user_info);
			$user_id = $user ? $user['user_id'] : -1;
			$user_type = $user ? $user['user_type'] : -1;
			$user_allow_pm = $user ? $user['user_allow_pm'] : -1;
			$this->user_list[] = $user;
		}
	}
	private function remove_user_from_user_list($user_id)
	{
		foreach ($this->user_list as $key => $user)
		{
			if ($user['user_id'] == $user_id)
			{
				$username = $user['username'];
				unset($this->user_list[$key]);
				return $username;
			}
		}
		return '';

	}

}
