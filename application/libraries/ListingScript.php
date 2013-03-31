<?php
/* Listing Script Class 25/03/11
 *
 * Collection of functions that gets information from the TMDB.org API and
 * outputs that data when needed.
 *
 * Functions
 * latestReview() - Outputs latest uploaded review into feature content areas
 *
 * top5Movies() - Outputs top 5 movies into left column areas on the pages
 * searchList() -
 * top10List() - Outputs the top 10 movies from TMBD.org to the index.php page
 * top10Genre() - Used in the nav bar to output a genre list
 * browseGenreList() - Retrives data based on the genre type selected ($genre)
 * browseOutput() - Formats and Outputs the retured result
 * getMovie() - Gets a single movies information based on passed movieID value
 * latestReview() - Function for outputting the latest uploaded review into the
 *                  feature content area
 * search() - Used to send the search value to TMDB.org API
 * 
 *  Last updated 17/05/11
 *  by Kieran Foxley-Jones
 */
define('api_key', '93a3f01925642df2c7898dc0d9fedcc7');

@include_once 'TMDb.php';
@include_once 'class.sqlHandler.userdb.php';

class listingScript {

    public static function latestReview() {
        /*
         * Function for outputting the latest uploaded review into the feature content
         * area in the index and movies page
         *
         * ToDo - Format better
         * ToDo - Output some user Info with review.
         */

        $query = "SELECT Mov_ID, Rev_ID
                    FROM link
                    ORDER BY Rev_ID DESC
                    LIMIT 1;";

        $movieResults = sqlHandler::getDB()->select($query);

        $query = "SELECT Rev_Content, Rev_Date, Rev_Rating, Rev_Quote
                                FROM reviews
                                WHERE Rev_ID = ".$movieResults[0]['Rev_ID'].";";

        $getReview = sqlHandler::getDB()->select($query);
        $getMovie = self::getMovie($movieResults[0]['Mov_ID']);


              echo  "<div id=\"contentFeatureWrap\">
                        <div id=\"contentFeaturePoster\">";
              if (isset($getMovie->movies->movie[0]->images->image[2]['url'])) {
                           echo "<img width=\"185\" height=\"278\"class=\"Poster\" src=\"".$getMovie->movies->movie[0]->images->image[2]['url']."\" alt=\"".$getMovie->movies->movie->name."\"></img>";
              }
              else {
                echo "<img src=\"images/no-poster.jpg\"></img>";

              }
              echo      "</div>
                        <div id=\"contentFeatureReview\">
                            <h2>".$getMovie->movies->movie->name."</h2>
                            <h3>".$getReview[0]['Rev_Quote']."</h3>
                            <p>".preg_replace("/rn/", "<br/>", stripslashes($getReview[0]['Rev_Content']))."</p>
                            <p>".$getReview[0]['Rev_Rating']."</p>
                        </div>
                    </div>";
    }

    public static function top5Movies() {
        /* Function to output the top 5 rate movies
         *
         * First Version: Top 5 rated by movies.Movie_Review_Rating
         * Second Version: Implement some kind of deprecation of the rating
         *                  Give new movies priority vs old
         */

        $query = "SELECT Mov_ID, Mov_Name
                    FROM movies
                    ORDER BY Mov_Review_Rating DESC
                    LIMIT 5;";

        $movieResults = sqlHandler::getDB()->select($query);

        $i = 0;

        while ($i < count($movieResults)) {

            echo "<a href=\"title.php?Mov_ID=".$movieResults[$i]['Mov_ID']."\">".$movieResults[$i]['Mov_Name']."</a><br />";
            $i++;
        }
    }



    public static function top10Genre() {

        /*  Outputs genre list into the nav menu */

        $tmdb_xml = new TMDb(api_key ,TMDb::XML);
        $xml_movie_results = $tmdb_xml->getGenres();
        if ($xml_movie_results != "") {
        $xml_movie_results = new SimpleXMLElement($xml_movie_results);

        $i = 0;
        while ($i < count($xml_movie_results->genres->genre)) {
            echo "<li><a href=\"movies.php?Genre_ID=".$xml_movie_results->genres->genre[$i]->id."\">".
                    $xml_movie_results->genres->genre[$i]->attributes()."</a></li>";

            $i++;
            }
        }
    }

    public static function browseOutput($xml) {

        /*Formats and Outputs the retured result*/

        $i = 0;
        $browseOutputData = '';
        while ($i < count($xml->movies->movie)) { //returns the amount of movie there are
            $browseOutputData .= "<div id=\"contentSingle\">\n";
            
            $name = $xml->movies->movie[$i]->name;
            $id = $xml->movies->movie[$i]->id;
            
            if (isset($xml->movies->movie[$i]->images->image[2]['url'])) {
                $poster = $xml->movies->movie[$i]->images->image[2]['url'];
                
                $anchorOut = "\n<img width=\"185\" height=\"278\"class=\"Poster\" src=\"".$poster."\" alt=\"".$name
                        ."\">\n</img>\n</div>\n<div id=\"contentTitle\"><strong>".$name."</a></strong></div>\n";
                
                $browseOutputData .= "<div id=\"contentPoster\">\n".anchor('main/title/'.$id, $anchorOut ,'title="'.$name.'"');
                        
            }
            else {                
                //this needs fixing
                $browseOutputData .= "<div id=\"contentPoster\"><a href=\"title.php?Mov_ID=".$id."\"><img src=\"images/no-poster.jpg\"></img></div>\n";
            }            
            
            $browseOutputData .= "</div>\n";

            $i++;
        }
        return $browseOutputData;
    }


    public static function browseGenreList($genre) {
        //Retrives data based on the genre type selected ($genre)
        $tmdb_xml = new TMDb(api_key ,TMDb::XML);
        $xml_movie_results = $tmdb_xml->browseMovies('rating','desc', 'per_page=10&page=1&min_votes=30&genres='.$genre);
        $xml_movie_results = new SimpleXMLElement($xml_movie_results);

        self::browseOutput($xml_movie_results);
    }




    public static function top10List(){
        /* Top 10 listing script, returns XML object to script that called it*/
        $tmdb_xml = new TMDb(api_key ,TMDb::XML);

        //Genre types to list (refer to http://api.themoviedb.org/2.1 )
        $genres = "12,28,16,35,80,105,18,82,14,36,9648,1115,878,53,37,10748,9805";

        //results can be ordered by using parameters as defined by 
        //http://api.themoviedb.org/2.1/methods/Movie.browse
        $xml_movie_results = $tmdb_xml->browseMovies('rating','desc', 'per_page=10&page=1&min_votes=30&genres='.$genres);
        $xml_movie_results = new SimpleXMLElement($xml_movie_results);

        self::browseOutput($xml_movie_results);
    }



    public static function getMovie($movieID) {

        /* Gets a single movies information based on passed movieID value */
            $tmdb_xml = new TMDb(api_key ,TMDb::XML);
            $xml_movie_results = $tmdb_xml->getMovie($movieID);

            $xml_movie_results = new SimpleXMLElement($xml_movie_results);

            return $xml_movie_results;
    }




    public static function search($title) {
        /* Used to send the search value to TMDB.org API
         */
        $tmdb_xml = new TMDb(api_key ,TMDb::XML);
        $xmlresults = $tmdb_xml->searchMovie($title);
        return (string) $xmlresults;
    }




    public static function searchList($searchString) {
        /*  Function that sends passed value to the TMDB.org API, which then
         *  returns results based on the value. Outputs the results to the
         *  movies.php page.
         */

        $xml_movie_results = self::Search($searchString);
        $xml_movie_results = new SimpleXMLElement($xml_movie_results);

        $i = 0;
        while ($i < count($xml_movie_results->movies->movie)) { //returns the amount of movie there are
            echo "<div id=\"contentSingle\">";
            if (isset($xml_movie_results->movies->movie[$i]->images->image[2]['url'])) {
                echo "<div id=\"contentPoster\"><a href=\"title.php?Mov_ID=".$xml_movie_results->movies->movie[$i]->id."\"
                ><img width=\"185\" height=\"278\"class=\"Poster\" src=\"".$xml_movie_results->movies->movie[$i]->images->image[2]['url']."\" alt=\"".$xml_movie_results->movies->movie[$i]->name."\"></img></div>";
            }
            else {
                echo "<div id=\"contentPoster\"><a href=\"title.php?Mov_ID=".$xml_movie_results->movies->movie[$i]->id."\"
                ><img src=\"images/no-poster.jpg\"></img></div>";
            }
            echo "<div id=\"contentTitle\"><strong>".$xml_movie_results->movies->movie[$i]->name."</a></a></strong></div>"; //outputs <name> in array position $i
            echo "</div>";

            $i++;
        }
    }
    
    function titleOutput($xmlData) {
        
        $output = '';
        $output = 
            '<div id="main_content">
                <div id="contentFeatureWrap">
                    <div id="contentFeaturePoster">';                       
        
                            if (isset($xmlData->movies->movie->images->image[2]['url'])) {
                                $output .= "<img width=\"185\" height=\"278\"class=\"Poster\" src=\"".
                                        $xmlData->movies->movie->images->image[2]['url']."\"></img><br />";
                            }
                            else {
                                $output .= "<img src=\"images/no-poster.jpg\"></img><br />";
                            } 
                    $output .= '</div>
                    <div id="contentFeatureReview">
                        <div id="contentMenuInfoWrap">
                               <a href=\"'.(isset($xmlData->movies->movie->trailer) ?
                                    $xmlData->movies->movie->trailer."\">Trailer</a><br />" : "No Trailer/n");
                     setlocale(LC_MONETARY, 'en_US');

                     $output .= 
                            "<h4>Info</h4>
                            Rating: ".$xmlData->movies->movie->certification."<br />
                            Budget: $".number_format((int)$xmlData->movies->movie->budget)."<br />
                            Revenue: $".number_format((int)$xmlData->movies->movie->revenue)."<br />
                            Website: <a href=\"".(isset($xmlData->movies->movie->homepage) ?
                                    $xmlData->movies->movie->homepage."\">Link</a><br />" : "No Website<br />");   
                        $output .= '</div><h2>';
                        $output .= $xmlData->movies->movie->name;
                        $output .= $xmlData->movies->movie->released;
                        $output .= '</h2>';
                        $output .= '<h3>' ;
                        $output .= "Rating: ".$xmlData->movies->movie->certification; 
                        $output .= '</h3>';
                        $output .= '<p>' ;
                        $output .= $xmlData->movies->movie->overview; 
                        $output .= '</p>';
                        $output .= '<p> |' ;
                        $output .= "<a href=\"".(isset($xmlData->movies->movie->trailer) ? $xmlData->movies->movie->trailer."\">Trailer</a>" : "No Trailer");
                        $output .=  '| Average Review Rating | </p>';
                    $output .= '</div>';
             $output .= '</div>';
             
             return $output;
    }
}
?>
