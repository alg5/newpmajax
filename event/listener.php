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

	public function __construct(\phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\auth\auth $auth, \phpbb\template\template $template, \phpbb\user $user, $phpbb_root_path, $php_ext, \phpbb\request\request_interface $request, $phpbb_container)
	{

		$this->template = $template;
		$this->user = $user;
		$this->auth = $auth;
		$this->db = $db;
		$this->config = $config;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
		$this->request = $request;
		$this->phpbb_container = $phpbb_container;

	}

	static public function getSubscribedEvents()
	{
		return array(
			'core.page_header_after'			=> 'page_header_after',
            //'core.search_get_topic_data'		=> 'search_get_topic_data',
            //'core.search_get_posts_data'		=> 'search_get_posts_data',
            //    //'core.search_modify_tpl_ary'		=> 'search_modify_tpl_ary',
		);
	}

	public function search_modify_tpl_ary($event)
	{
		//print_r($event['tpl_ary']);
		//print_r($event['replies']);
			$tpl_ary = $event['tpl_ary'];

			$tpl_ary = array_merge($tpl_ary, array(
//				'SEARCH_MATCHES'	=> $event['replies'],
				'SEARCH_MATCHES'	=> 111,
			));

			$event['tpl_ary'] = $tpl_ary;
	}

	public function page_header_after($event)
	{
		$this->user->add_lang_ext('alg/newpmajax', 'newpmajax');
		$this->template->assign_vars(array(
			'U_NEWPMAJAX_PATH'		        => append_sid("{$this->phpbb_root_path}newpmajax/"),

			));

	}

	public function search_get_topic_data($event)
	{
		$ls = $this->request->variable('ls', 0);
		if ($ls == 0)
		{
			return;
		}
		$forum_id = $this->request->variable('forum_id', 0);
		$author_id = $this->request->variable('author_id', 0);
		$start = $this->request->variable('start', 0);
		$per_page = $this->config['posts_per_page'];

		$sql_where = $event['sql_where'];
		$sql_select = $event['sql_select'];
		//print($sql_select);
		$sql_from = $event['sql_from'];
		//$sql_select .= ', count(t.topic_id) as total_count  ';
		//$event['sql_select'] = $sql_select;
		//$sql_from = "phpbb_topics t LEFT JOIN phpbb_forums f ON (f.forum_id = t.forum_id) LEFT JOIN phpbb_topics_posted tp ON (tp.user_id = 54 AND t.topic_id = tp.topic_id) LEFT JOIN phpbb_topics_track tt ON (tt.user_id = 54 AND t.topic_id = tt.topic_id) LEFT JOIN phpbb_forums_track ft ON (ft.user_id = 54 AND ft.forum_id = f.forum_id)";
		//$sql_where ='t.topic_id IN (58, 5500, 5502, 638, 214, 987, 4811, 5171, 366, 2788, 250, 4991, 5499, 5497, 5163, 474, 3185, 388, 5490, 5494, 286, 5493, 909, 5488, 2628, 5231, 5246, 2362, 4823, 5176, 5173) AND (f.forum_id NOT IN (57, 169, 181, 200, 226, 262, 312, 339, 355, 365, 366, 368, 378, 379, 381, 383, 387, 423) OR f.forum_id IS NULL) AND ((t.forum_id NOT IN (57, 200, 423, 169, 181, 226, 262, 312, 339, 355, 365, 366, 368, 378, 379, 381, 383, 387) AND t.topic_visibility = 1) OR t.forum_id IN (1, 2, 3, 4, 5, 6, 7, 8, 9, 11, 12, 13, 14, 15, 16, 17, 18, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 43, 44, 45, 46, 47, 48, 49, 50, 51, 52, 53, 54, 55, 56, 58, 59, 60, 61, 62, 63, 64, 65, 66, 67, 68, 69, 70, 71, 72, 73, 75, 76, 77, 78, 79, 80, 81, 82, 84, 89, 90, 91, 92, 93, 94, 95, 100, 101, 102, 103, 104, 106, 107, 108, 109, 110, 111, 112, 113, 114, 115, 116, 117, 156, 157, 158, 160, 161, 162, 166, 167, 170, 177, 178, 182, 184, 187, 188, 192, 194, 196, 197, 198, 199, 201, 202, 203, 204, 205, 206, 209, 210, 211, 212, 213, 214, 215, 216, 217, 218, 219, 223, 224, 225, 227, 228, 229, 230, 242, 243, 244, 245, 246, 247, 248, 249, 250, 251, 252, 253, 254, 255, 256, 257, 258, 259, 260, 261, 275, 276, 277, 278, 305, 306, 307, 308, 309, 310, 311, 313, 314, 315, 317, 318, 319, 320, 321, 322, 323, 324, 325, 326, 327, 328, 329, 330, 331, 332, 333, 334, 335, 336, 337, 338, 340, 341, 342, 343, 344, 345, 346, 347, 348, 349, 350, 351, 352, 353, 354, 356, 362, 363, 364, 367, 369, 370, 371, 372, 373, 374, 375, 376, 377, 380, 382, 384, 385, 386, 388, 389, 390, 391, 392, 393, 394, 395, 396, 397, 398, 399, 400, 401, 402, 403, 404, 405, 406, 407, 408, 409, 410, 411, 412, 413, 414, 415, 416, 417, 418, 419, 420, 421, 422, 424, 425, 426, 427, 428, 429, 430, 431))';
		//$sql_where .= ' AND t.topic_poster = ' . $author_id . $this->build_subforums_search($forum_id);
		$sql_where = " t.topic_status <> " . ITEM_MOVED .
			" AND t.topic_visibility = " . ITEM_APPROVED .
				" AND t.topic_poster = " . $author_id . $this->build_subforums_search($forum_id);
		$event['sql_where'] = $sql_where;
		//$sql = "SELECT" .  $event['sql_select'] . "  FROM " . $event['$sql_from'] . " WHERE  " . $sql_where;
		// print_r($sql_from);

	}
	//SELECT t.*, f.forum_id, f.forum_name, tp.topic_posted, tt.mark_time, ft.mark_time as f_mark_time FROM phpbb_topics t LEFT JOIN phpbb_forums f ON (f.forum_id = t.forum_id) LEFT JOIN phpbb_topics_posted tp ON (tp.user_id = 54 AND t.topic_id = tp.topic_id) LEFT JOIN phpbb_topics_track tt ON (tt.user_id = 54 AND t.topic_id = tt.topic_id) LEFT JOIN phpbb_forums_track ft ON (ft.user_id = 54 AND ft.forum_id = f.forum_id) WHERE t.topic_id IN (58, 5500, 5502, 638, 214, 987, 4811, 5171, 366, 2788, 250, 4991, 5499, 5497, 5163, 474, 3185, 388, 5490, 5494, 286, 5493, 909, 5488, 2628, 5231, 5246, 2362, 4823, 5176, 5173) AND (f.forum_id NOT IN (57, 169, 181, 200, 226, 262, 312, 339, 355, 365, 366, 368, 378, 379, 381, 383, 387, 423) OR f.forum_id IS NULL) AND ((t.forum_id NOT IN (57, 200, 423, 169, 181, 226, 262, 312, 339, 355, 365, 366, 368, 378, 379, 381, 383, 387) AND t.topic_visibility = 1) OR t.forum_id IN (1, 2, 3, 4, 5, 6, 7, 8, 9, 11, 12, 13, 14, 15, 16, 17, 18, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 43, 44, 45, 46, 47, 48, 49, 50, 51, 52, 53, 54, 55, 56, 58, 59, 60, 61, 62, 63, 64, 65, 66, 67, 68, 69, 70, 71, 72, 73, 75, 76, 77, 78, 79, 80, 81, 82, 84, 89, 90, 91, 92, 93, 94, 95, 100, 101, 102, 103, 104, 106, 107, 108, 109, 110, 111, 112, 113, 114, 115, 116, 117, 156, 157, 158, 160, 161, 162, 166, 167, 170, 177, 178, 182, 184, 187, 188, 192, 194, 196, 197, 198, 199, 201, 202, 203, 204, 205, 206, 209, 210, 211, 212, 213, 214, 215, 216, 217, 218, 219, 223, 224, 225, 227, 228, 229, 230, 242, 243, 244, 245, 246, 247, 248, 249, 250, 251, 252, 253, 254, 255, 256, 257, 258, 259, 260, 261, 275, 276, 277, 278, 305, 306, 307, 308, 309, 310, 311, 313, 314, 315, 317, 318, 319, 320, 321, 322, 323, 324, 325, 326, 327, 328, 329, 330, 331, 332, 333, 334, 335, 336, 337, 338, 340, 341, 342, 343, 344, 345, 346, 347, 348, 349, 350, 351, 352, 353, 354, 356, 362, 363, 364, 367, 369, 370, 371, 372, 373, 374, 375, 376, 377, 380, 382, 384, 385, 386, 388, 389, 390, 391, 392, 393, 394, 395, 396, 397, 398, 399, 400, 401, 402, 403, 404, 405, 406, 407, 408, 409, 410, 411, 412, 413, 414, 415, 416, 417, 418, 419, 420, 421, 422, 424, 425, 426, 427, 428, 429, 430, 431)) ORDER BY t.topic_last_post_time DESC

	public function search_get_posts_data($event)
	{
		$ls = $this->request->variable('ls', 0);
		if ($ls == 0)
		{
			return;
		}

		$forum_id = $this->request->variable('forum_id', 0);
		$topic_id = $this->request->variable('topic_id', 0);
		if ($forum_id == 0 && $topic_id == 0)
		{
			return;
		}

		$sql_array = $event['sql_array'];
		$where = $sql_array['WHERE'];
		if ($topic_id)
		{
			$where .= ' AND p.topic_id = ' . $topic_id;
		}
		$sql_array['WHERE'] = $where;

		$event['sql_array'] = $sql_array;
		// print_r($event['sql_array']['WHERE']);
	}

	private function build_subforums_searchOld($forum_id)
	{
		if ($forum_id == 0)
		{
			return '';
		}
		$sql = "SELECT left_id, right_id " .
				" FROM " . FORUMS_TABLE .
				" WHERE forum_id = " . $forum_id ;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return ' AND (f.left_id  >= ' . $row['left_id'] . '  AND f.right_id <= ' .  $row['right_id'] . ' )';
	}

	private function build_subforums_search($forum_id)
	{
		if ($forum_id == 0)
		{
			return '';
		}
		$sql = "SELECT left_id, right_id " .
				" FROM " . FORUMS_TABLE .
				" WHERE forum_id = " . $forum_id ;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		$sql = "SELECT forum_id " .
				" FROM " . FORUMS_TABLE .
				" WHERE left_id >= " . $row['left_id'] .
				" AND right_id <= " .  $row['right_id'] .
				" ORDER BY  left_id" ;
		$result = $this->db->sql_query($sql);

		$subforums = ' AND t.forum_id IN (';
		while ($row = $this->db->sql_fetchrow($result))
		{
			$subforums .= ( $row['forum_id'] . ',');
		}
		$subforums = substr($subforums, 0, -1) . " )";
		return $subforums;
	}

}
