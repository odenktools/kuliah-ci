<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * MY_Form_validation Class
 *
 * Extends Form_Validation library
 *
 * Adds one validation rule, "unique" and accepts a
 * parameter, the name of the table and column that
 * you are checking, specified in the forum table.column
 *
 * Note that this update should be used with the
 * form_validation library introduced in CI 2.0.2
 */

class MY_Form_validation extends CI_Form_validation
{

    /**
     * Override Property
     *
     **/
    public $CI;

    function __construct()
    {
        $this->CI =& get_instance();

        parent::__construct();

        $this->CI->load->language('extra_validation');
    }

    // --------------------------------------------------------------------

    /**
     * Data harus unik (tidak boleh ada yang sama)
     *
     * Examples :
     * ==================================
     * <code>
     *
     * unique_records[tablename.username]
     *
     * </code>
     *
     * @access    public
     * @param    string
     * @param    field
     * @return    bool
     *
     */
    public function unique_records($str, $field)
    {
        $this->CI->load->database();

        list($table, $column) = explode(".", $field, 2);

        $this->CI->form_validation->set_message('unique_records', $this->CI->lang->line('unique_records'));

        $query = $this->CI->db->query("SELECT $column FROM $table WHERE $column = '$str' limit 0, 1");

        return ($query->num_rows() > 0) ? FALSE : TRUE;

    }
	
    public function unique_records_after($str, $field)
    {
        $this->CI->load->database();
		
        list($table, $column) = explode(".", $field, 2);
		
        $this->CI->form_validation->set_message('unique_records_after', $this->CI->lang->line('unique_records_after'));
		
        $query = $this->CI->db->query(
		"
			SELECT
			$column
			FROM $table
			WHERE $column NOT IN (SELECT DISTINCT
			($column)
			FROM $table
			WHERE $column = 'testing');"
		);
		
        return ($query->num_rows() > 0) ? FALSE : TRUE;
		
    }
	
    /**
     * @param    string
     * @param    string
     * @return    string
     *
     * http://www.michaelwales.com/2010/02/basic-pattern-matching-form-validation-in-codeigniter/
     *
     **/
    function matches_pattern($str, $pattern)
    {

        $characters = array(
            '[', ']', '\\', '^', '$',
            '.', '|', '+', '(', ')',
            '#', '?', '~' // Our additional characters

        );

        $regex_characters = array(
            '\[', '\]', '\\\\', '\^', '\$',
            '\.', '\|', '\+', '\(', '\)',
            '[0-9]', '[a-zA-Z]', '.' // Our additional characters
        );

        $pattern = str_replace($characters, $regex_characters, $pattern);
        if (preg_match('/^' . $pattern . '$/', $str)) return TRUE;
        return FALSE;

    }

    /**
     *
     * @param string
     *
     */
    public function alpha_dot_dash($str)
    {
        if (!preg_match("/^([-a-z0-9_\-\.])+$/i", $str)) {

            $this->CI->form_validation->set_message("only_url", $this->CI->lang->line('only_url'));

            return false;

        } else {

            return TRUE;
        }


    }

    /**
     * hanya memperbolehkan number pada input
     * @param string
     *
     */
    public function only_number($str)
    {

        if (!preg_match("/^[0-9\ ]+$/", $str)) {

            $this->CI->form_validation->set_message('only_number', 'The %s only accept number');

            return FALSE;

        } else {

            return TRUE;
        }

    }

    /**
     * hanya memperbolehkan hurug pada input
     * @param string
     *
     */
    public function only_letter($str)
    {

        if (!preg_match("/^[a-zA-Z\ \']+$/", $str)) {

            $this->CI->form_validation->set_message('only_letter', $this->CI->lang->line('only_letter'));

            return FALSE;

        } else {

            return TRUE;
        }

    }

    /**
     * hanya memperbolehkan huruf dan angka pada input
     * @param string
     *
     */
    public function onlyLetterNumber($str)
    {

        if (!preg_match("/^[0-9a-zA-Z]+$/", $str)) {

            $this->CI->form_validation->set_message('onlyLetterNumber', $this->CI->lang->line('onlyLetterNumber'));

            return FALSE;

        } else {

            return TRUE;
        }
    }

    /**
     * hanya memperbolehkan huruf, 0-9 dan spasi pada input
     * @param string
     *
     */
    public function letterNumberSpaces($str)
    {

        if (!preg_match("/^(?=.*[A-Za-z0-9])[A-Za-z0-9 _]*$/", $str)) {

            $this->CI->form_validation->set_message('letterNumberSpaces', $this->CI->lang->line('letterNumberSpaces'));

            return FALSE;

        } else {

            return TRUE;
        }
    }
	
    /**
     * hanya memperbolehkan huruf dan spasi pada input
     * @param string
	 *
     */
    public function letterSpaces($str)
    {
		
        if (!preg_match("/^(?=.*[A-Za-z])[A-Za-z _]*$/", $str)) {
			
            $this->CI->form_validation->set_message('letterSpaces', $this->CI->lang->line('letterSpaces'));
			
            return FALSE;
			
        } else {
			
            return TRUE;
        }
    }
    /**
     * hanya memperbolehkan url pada input
     * @param string
     *
     */
    public function only_url($str)
    {
		if (!preg_match("~^(http|ftp)(s)?\:\/\/((([a-z|0-9|\-]{1,25})(\.)?){2,7})($|/.*$)~i", $str)) {
			
            $this->CI->form_validation->set_message('only_url', $this->CI->lang->line('only_url'));
			
            return FALSE;
			
        } else {
			
            return TRUE;
        }
    }

    /**
     * validasi format tanggal (dd-mm-yyyy)
     *
     **/
    public function valid_date($str)
    {
        if (!preg_match("/^(0[1-9]|1[0-9]|2[0-9]|3[01])-(0[1-9]|1[012])-([0-9]{4})$/", $str)) {

            $this->CI->form_validation->set_message('valid_date', $this->CI->lang->line('valid_date'));

            return FALSE;
        } else {
            return TRUE;
        }

    }

    public function check_math_question($str)
    {

        $questionOne = $this->input->post('q1');
        $questionTwo = $this->input->post('q2');

        $correct_answer = $questionOne + $questionTwo;

        if ($str != $correct_answer) {

            $this->CI->form_validation->set_message('check_math_question', 'The %s answer to the math question was incorrect.');

            return FALSE;

        } else {

            return TRUE;
        }

    }

}

?>