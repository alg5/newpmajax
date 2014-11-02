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
		$this->warning = array(); // save warning in here
		$this->error = array(); // save errors in here

	}

	public function main($action, $forum, $user)
	{
		// Grab data
        //$q = utf8_strtoupper(utf8_normalize_nfc($this->request->variable('q', '',true)));

        $this->user->add_lang_ext('alg/newpmajax', 'newpmajax');

		switch ($action)
		{
			case 'add_to':
			case 'add_bcc':
				$this->add_sender($action);
			break;


			default:
				$this->error[] = array('error' => $this->user->lang['INCORRECT_SEARCH']);

		}
		if (sizeof($this->error))
		{
			$return_error = array();
			foreach($this->error as $cur_error)
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
        add_form_key('ucp_pm_compose');
	    // Grab only parameters needed here
	    $to_user_id		=   request_var('u', 0);
	    $to_group_id	= request_var('g', 0);
        $group_list = request_var('group_list', array(0));
        $this->group_list = request_var('group_list', array(0));
        $this->user_list = array();
        //$username_list = request_var('username_list', '', true);
        $username_list = $this->request->variable('username_list', '', true);
        //$remove_u	= (isset($_REQUEST['remove_u'])) ? true : false;
        //$remove_g	= (isset($_REQUEST['remove_g'])) ? true : false;
        $remove_u	= false;
	    $remove_g	= false;
        
        //todo what is it?
	    //$usernames = request_var('username', '', true);
	    //$usernames = (empty($usernames)) ? array() : array($usernames);
        $usernames =  array();
	    $username_list = request_var('username_list', '', true);
	    if ($username_list)
	    {
		    //$usernames = array_merge($usernames, explode("\n", $username_list));
		    $usernames =  explode("\n", $username_list);
		    $this->user_list =  explode("\n", $username_list);
	    }


	    $add_to = $action == "add_to" ? true : false;
	    $add_bcc	=  $action == "add_bcc" ? true : false;

        
	    $this->address_list	= $this->request->variable('address_list', array('' => array(0 => '')));
           // print_r($address_list);
         if (sizeof($usernames))
		{
			$user_id_ary = array();
			user_get_id_name($user_id_ary, $usernames, array(USER_NORMAL, USER_FOUNDER, USER_INACTIVE));

            $this->get_user_id_by_name($usernames);
        }
        $this->remove_existing_recipients($action);
           
           //*****************************************
           //*****************************************
           //*****************************************

	    $select_single = ($this->config['allow_mass_pm'] && $this->auth->acl_get('u_masspm')) ? false : true;
	    $current_time = time();
        
        // we include the language file here
	   // $this->user->add_lang('viewtopic');
 
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
        if (sizeof($group_list)  && (!$this->config['allow_mass_pm'] || !$this->auth->acl_get('u_masspm_group')))
	    {
            $this->error[] = array('error' => $this->user->lang['NO_AUTH_GROUP_MESSAGE']);
	    }        
        
        // Check mass pm to users permission
        $num_recipients = num_recipients($this->address_list);
	    if ((!$this->config['allow_mass_pm'] || !$this->auth->acl_get('u_masspm')) && num_recipients($this->address_list) > 1)
	    {
        //    $address_list = get_recipients($address_list, 1);
        //    $error[] = $user->lang('TOO_MANY_RECIPIENTS', 1);
        }
        
        // Check for too many recipients
	    if (!empty($this->address_list['u']) && $max_recipients && sizeof($this->address_list['u']) > $max_recipients)
	    {
            //$address_list = get_recipients($address_list, $max_recipients);
            //$error[] = $user->lang('TOO_MANY_RECIPIENTS', $max_recipients);
	    }
        
        
        if( !sizeof($this->address_list))
        {
            //$address_list = rebuild_header(array('to' => $post['to_address'], 'bcc' => $post['bcc_address']));
            //$addr = rebuild_header(array('to' => $post['to_address'], 'bcc' => $post['bcc_address']));
        }
        
        
        $type = ($add_to) ? 'to' : 'bcc';
        if (sizeof($group_list))
		{
			foreach ($group_list as $group_id)
			{
				//$address_list['g'][$group_id] = $type;
			}
		}

		        // User ID's to add...
		        $user_id_ary = array();

		        // Reveal the correct user_ids
		        if (sizeof($usernames))
		        {
			        $user_id_ary = array();
			        user_get_id_name($user_id_ary, $usernames, array(USER_NORMAL, USER_FOUNDER, USER_INACTIVE));

                    
			        // If there are users not existing, we will at least print a notice...
                    //if (!sizeof($user_id_ary))
                    //{
                    //    $this->error[] = array('error' => $this->user->lang['PM_NO_USERS']);
                    //}
                    //else
                    {
		                $can_ignore_allow_pm = $this->auth->acl_gets('a_', 'm_') || $this->auth->acl_getf_global('m_');

		                // Administrator deactivated users check and we need to check their
		                //		PM status (do they want to receive PM's?)
		                // 		Only check PM status if not a moderator or admin, since they
		                //		are allowed to override this user setting
		                $sql = 'SELECT user_id, user_allow_pm
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
				                $removed_no_pm = true;
			                }
			                else
			                {
				                $removed_no_permission = true;
			                }

			                unset($user_id_ary[$row['user_id']]);
		                }
		                $this->db->sql_freeresult($result);
		                // print a notice about users not being added who do not want to receive pms
		                if ($removed_no_pm)
		                {
                            $this->error[] = array('error' => $this->user->lang['PM_USERS_REMOVED_NO_PM']);
		                }

		                // print a notice about users not being added who do not have permission to receive PMs
		                if ($removed_no_permission)
		                {
                            $this->error[] = array('error' => $this->user->lang['PM_USERS_REMOVED_NO_PERMISSION']);
		                }

		                if (!sizeof(array_keys($this->address_list['u'])))
		                {
                            $this->error[] = array('error' => $this->user->lang['PM_NO_USERS']);
		                }
                        else
                        {
		                    // Check if users have permission to read PMs
		                    $can_read = $this->auth->acl_get_list(array_keys($this->address_list['u']), 'u_readpm');
		                    $can_read = (empty($can_read) || !isset($can_read[0]['u_readpm'])) ? array() : $can_read[0]['u_readpm'];
		                    $cannot_read_list = array_diff(array_keys($this->address_list['u']), $can_read);
		                    if (!empty($cannot_read_list))
		                    {
			                    foreach ($cannot_read_list as $cannot_read)
			                    {
				                    unset($this->address_list['u'][$cannot_read]);
			                    }

			                    $error[] = $user->lang['PM_USERS_REMOVED_NO_PERMISSION'];
		                    }

		                    // Check if users are banned
		                    $banned_user_list = phpbb_get_banned_user_ids(array_keys($this->address_list['u']), false);
		                    if (!empty($banned_user_list))
		                    {
			                    foreach ($banned_user_list as $banned_user)
			                    {
				                    unset($this->address_list['u'][$banned_user]);
			                    }

			                    $error[] = $user->lang['PM_USERS_REMOVED_NO_PERMISSION'];
		                    }                                            
                        }
                        
    
                        
                        

                    }//sizeof username
		        }        
        
                //build output
                
        
        
        

         $this->return = array(
            'GROUP_LIST'		=> $group_list,
            'USER_LIST'		=> $this->user_list,
            'USERNAME_LIST'		=> $username_list,
            'ADDRESS_LIST'		=> $this->address_list,
            'NUM_RECIPIENTS'		=> $num_recipients,
            'USERNAMES'		=> $usernames,
            'USER_ID_ARY'		=> $user_id_ary,

            
            'S_SHOW_PM_BOX'		=> true,
            'S_ALLOW_MASS_PM'	=> ($this->config['allow_mass_pm'] && $this->auth->acl_get('u_masspm')) ? true : false,
            'S_GROUP_OPTIONS'	=> ($this->config['allow_mass_pm'] && $this->auth->acl_get('u_masspm_group')) ? $group_options : '',
            'U_FIND_USERNAME'	=> append_sid("{$this->phpbb_root_path}memberlist.$this->php_ext", "mode=searchuser&amp;form=postform&amp;field=username_list&amp;select_single=$select_single"),
        );


    }
    
    private function build_new_hidden_field($address_list)
    {
	    $s_hidden_address_field = '';
	    foreach ($address_list as $type => $adr_ary)
	    {
		    foreach ($adr_ary as $id => $field)
		    {
			    $s_hidden_address_field .= '<input type="hidden" name="address_list[' . (($type == 'u') ? 'u' : 'g') . '][' . (int) $id . ']" value="' . (($field == 'to') ? 'to' : 'bcc') . '" />';
		    }
	    }
		    foreach ($adr_ary as $id => $field)
		    {
			    $s_hidden_address_field .= '<input type="hidden" name="address_list[' . (($type == 'u') ? 'u' : 'g') . '][' . (int) $id . ']" value="' . (($field == 'to') ? 'to' : 'bcc') . '" />';
		    }
        
        
        
	    return $s_hidden_address_field;
    }
    
    private function remove_existing_recipients($action)
    {
        if (!sizeof($this->address_list))
        {
            return ;
        }
        $action_value = $action == 'add_to' ? 'to'  : 'bcc';
        
        if (sizeof($this->address_list['u']) && sizeof($this->username_list))
        {
            foreach ($this->username_list as $username)
            {
                if (is_item_exists($user_id, $this->address_list['u']))
                {
                     unset($this->user_id_ary[$user_id]);
                     $this->warning[] = array('warning' => $this->user->lang['PM_USERS_REMOVED_NO_PM']);
                }
            }
        }
        if (sizeof($this->address_list['g']) && sizeof($this->group_list))
        {
            foreach ($this->group_list as $group_id)
            {
                if (is_item_exists($group_id, $this->group_list))
                {
                     unset($this->group_list[$group_id]);
                     $this->warning[] = array('warning' => $this->user->lang['PM_USERS_REMOVED_NO_PM']);
                }
            }
        }
    
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
    private function get_user_id_by_name($username_ary, $user_type = false)
    {

        $which_ary =  'username_ary';

        if ($$which_ary && !is_array($$which_ary))
        {
            $$which_ary = array($$which_ary);
        }

	    $sql_in = array_map('utf8_clean_string', $$which_ary);
	    unset($$which_ary);

	    $user_id_ary = $username_ary = array();

	    // Grab the user id/username records
	    $sql_where = 'username_clean';
	    $sql = 'SELECT user_id, username
		    FROM ' . USERS_TABLE . '
		    WHERE ' . $this->db->sql_in_set($sql_where, $sql_in);

        //if ($user_type !== false && !empty($user_type))
        //{
        //    $sql .= ' AND ' . $db->sql_in_set('user_type', $user_type);
        //}

        $result = $this->db->sql_query($sql);
		                

        //if (!($row = $db->sql_fetchrow($result)))
        //{
        //    $db->sql_freeresult($result);
        //    return 'NO_USERS';
        //}
        $user_info = array();
        $user_count = 0;
						
		while ($row = $this->db->sql_fetchrow($result))
	    {
		    $user_info[] = $row;
		    $user_count ++;
	    }
        $this->db->sql_freeresult($result);   
        
        foreach($username_ary as $username)
        {
            $user = $this->is_user_exists($username, $user_info);
            $user_id = $user ? $user['user_id'] : -1;
           $this->user_list = array(
             'username'		=> $username,
             'user_id'		=> $user_id,
          );
        }
        
        
        

    //do
    //{
    //    $username_ary[$row['user_id']] = $row['username'];
    //    $user_id_ary[] = $row['user_id'];
    //}
    //while ($row = $db->sql_fetchrow($result));
    //$db->sql_freeresult($result);

	return false;
}

}
