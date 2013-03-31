    <div id="main_content">
        <?php 
        echo $output;
        
        if ($sql_results != FALSE) {
            foreach ($sql_results->result_array() as $row) {
        ?>
        <div class="revIndivWrapper">
            <div class="usrProfile">
                <div class="usrPic"><img src="images/profileimages/default.png"></img>
                </div>
                <strong>
                <?php echo $row['username']; ?>
                </strong>
                Posted: "<?php echo $row['review_date']; ?>
            </div>
            <div class="revContent">
                <h3>
                <?php echo $row['review_quote']; ?>
                </h3>
                <p>
                <?php echo $row['review_content']; ?>
                </p>
            </div>
        </div>
        <?php
            }
        }
        else {
            echo "No Reviews";
        }
            $this->load->view('main/review_form');
        ?>
    </div>
