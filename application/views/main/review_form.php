<div class="review_form">
    <?php 
        echo form_open('main/review_upload');
        echo form_hidden('movie_id', isset($movie_id) ? $movie_id :$this->uri->segment(3));
        echo form_input('review_quote', set_value('review_quote', 'Review Quote'));
        echo form_input('review_content', set_value('review_content', 'Review Content'));
        echo form_input('review_rating', set_value('review_rating', 'Review Rating'));
        echo form_submit('submit','Submit');
        
        echo validation_errors('<p class="error">');
        echo form_close();
    ?>
</div>
