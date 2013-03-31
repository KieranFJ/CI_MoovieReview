<?php

class Main extends CI_Controller {
    
    const apikey = '93a3f01925642df2c7898dc0d9fedcc7';
    
    
    function index() {
        $this->load->library('TMDb');
        $this->load->library('ListingScript');      
        
        
        $tmdb_attrib = new TMDb(self::apikey, TMDb::XML);        
        $genres = "12,28,16,35,80,105,18,82,14,36,9648,1115,878,53,37,10748,9805";        
        $xml_movie_results = $tmdb_attrib->browseMovies('rating','desc', 'per_page=10&page=1&min_votes=30&genres='.$genres);
        $xml_movie_results = new SimpleXMLElement($xml_movie_results);
        
        $data['output'] = ListingScript::browseOutput($xml_movie_results);

        
        $data['load_page'] = 'main/front_page';
        $this->load->view('includes/template', $data);
    }
    
    function title($movie_id) {
        $this->load->library('TMDb');
        $this->load->library('ListingScript');
        
        if (isset($movie_id)) {
            $title = $movie_id;
            
        }
        else {
            $title = $this->uri->segment(3);
        }
            
        $tmdb_attrib = new TMDb(self::apikey, TMDb::XML);        
        $xml_movie_results = $tmdb_attrib->getMovie($title);
        $send = new SimpleXMLElement($xml_movie_results);        
        
        $this->load->model('reviews_model');
        $query = $this->reviews_model->load_reviews($title);
        
        $data['output'] = ListingScript::titleOutput($send);
        $data['load_page'] = 'main/title';
        $data['sql_results'] = $query;
        $data['movie_id'] = $title;
        $this->load->view('includes/template', $data);
    }
    
    function review_upload() {
        
        $movie_id = $_POST['movie_id'];
        
        //@todo validation for logged in account
        //if ($this->session->userdata('is_logged_in') == FALSE) {
        //    $this->title($movie_id);
        //}        
        
        $this->load->model('reviews_model');
        
        $this->load->library('form_validation');
        
        $this->form_validation->set_rules('review_content', 'Review Content', 'required|max_length[1000]');
        $this->form_validation->set_rules('review_quote', 'Review Summary', 'required|max_length[1000]');
        $this->form_validation->set_rules('review_rating', 'Review Rating', 
                                            'required|numeric|exact_length[1]|greater_than[0]|less_than[10]');
        
        if ($this->form_validation->run() == FALSE) {
            
            $this->title($movie_id);
            
        }
        else {
            $this->reviews_model->insert_review($movie_id);
        }
    }
}
    
    
?>
