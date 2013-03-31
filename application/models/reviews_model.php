<?php

class Reviews_model extends CI_Model {
    
    function load_reviews($title) {        
        $query = $this->db->query("SELECT reviews.review_id, review_content, review_date, review_rating, review_quote, membership.username
                                FROM reviews, membership, link
                                WHERE link.review_id = reviews.review_id
                                AND link.movie_id = ".$title."
                                AND link.User_ID = membership.user_id;");        
        if ($query->num_rows > 0) {                
            return $query;
        }
        else {
            return FALSE;
        }        
    }
    
    function insert_review($movie_id) {
        
        if ($this->session->userdata('is_logged_in') == true) {
        
            $success = FALSE;            
            
            $user_id = $this->session->userdata('username');
            $data = array(
                'review_content' => $_POST['review_content'],
                'review_rating' => $_POST['review_rating'],
                'review_quote' => $_POST['review_quote']);

            if (isset($data)) {

                $this->db->insert('reviews', $data);
                
                $linkdata = array (
                    'movie_id' => $movie_id,
                    'user_id' => '1',
                    'review_id' => $this->db->insert_id());

                $this->db->insert('link', $linkdata);
            }
            redirect('main/title/'.$movie_id);
        }
        else {
            redirect('main');
            //@todo redirect to login page / request
        }
    }
    
    
}
?>
