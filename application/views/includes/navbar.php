            <div id="navigation">
                <div id="navBar">
                </div>
                <div id="navSearch">
                    
                    <?php                     
                        
                        echo form_open('search/movie_search');
                        echo form_input('search', 'Search');
                        echo form_submit('submit', 'Go');
                        echo form_close();
                    ?>
                  

                </div>
            </div>