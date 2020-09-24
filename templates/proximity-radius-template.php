<?php get_header(); ?>
		<?php wp_body_open(); ?>

		<?php while ( have_posts() ) : ?>

			<?php the_post(); ?>

			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

				<form method="get" action="<?php echo get_permalink(); ?>">
			    <div>
			        <span>Find a School within</span>
			        <input name="proximity" type="number" placeholder="15" value="<?php echo $_GET['proximity'] ?>" />
			            <select name="units">
			                <option value="Miles" <?php echo $_GET['units'] === 'Miles' ? 'selected' : null; ?>>Miles</option>
			                <option value="K" <?php echo $_GET['units'] === 'K' ? 'selected' : null; ?>>Km</option>
			            </select>
			            <span>from</span>
			        <input name="origin" type="text" placeholder="Your Address" value="<?php echo $_GET['origin'] ?>" />
			    </div>
			    <div>
			        <input type="submit" value="Search" />
			        <a href="<?php echo get_permalink(); ?>">Reset</a>
			    </div>
			</form>

			<?php

			// get the parameters from the URL
			// these parameter names come from the 'name' attribute of each input in the form
			$proximity = isset($_GET['proximity']) ? $_GET['proximity'] : null;
			$origin = isset($_GET['origin']) ? $_GET['origin'] : null;
			$unit = isset($_GET['units']) ? $_GET['units'] : null;

			    // create an empty array to store results for a later query
			    $results = array();

			    // only run this query if a user has made a search
			    if ($origin) {

			        $proximity_query = new WP_Query(array(
			                'post_type' 		=> 'schools', /* this is the name of our custom post type */
			                'posts_per_page'	=> -1
			        ));

			        // loop over each result
			        // and calculate if it's in the proximity
			        if($proximity_query->have_posts()) {
			            while($proximity_query->have_posts()) {
			                $proximity_query->the_post();

			                // this is the name of our custom field
			                $address = get_field('address');

			                if ($address) {
			                    // calculate distance using our function
			                    // the $origin values is from the url parameters
			                    $distance = YOUR_THEME_NAME_get_distance($origin, $address['lat'], $address['lng'], $unit);

			                    // if the distance is less than our threshold,
			                    // then we are going to add it to our $results array
			                    // need to use (float) because the original values are strings.
			                    if ((float)$distance <= (float)$proximity) {
			                        array_push($results, get_the_ID());
			                    }

			                }
			            }
			        }

			        // reset the $proximity_query
			        wp_reset_postdata();

			    }

			    // a search was made, and there are results in the '$results' array
			    if($results && $proximity) {
			        $results_args = array(
			            'post_type' 	    => 'schools',
			            'post__in' 		    => $results /* we use post__in to find only the posts that are in the '$results' array */
			        );
			    // a search was made, but there are no results in the '$results' array
			    } else if (!$results && $proximity) {
			        $results_args = array();
			    // no search was made, so show all posts
			    } else {
			        $results_args = array(
			            'post_type' 		=> 'schools',
			            'posts_per_page' 	=> -1
			        );
			    }

			    // create a new query to display the results
			    $results_query = new WP_Query($results_args);
			    if($results_query->have_posts()) {
			        // if the user made a search, add a column for distance
			        echo $origin ? '<div>' : '<div>';
			        while($results_query->have_posts()) {
			            $results_query->the_post();
			            $address = get_field('address');
			            $distance = YOUR_THEME_NAME_get_distance($origin, $address['lat'], $address['lng'], $unit);
									$locations = get_the_terms( $post->ID, 'location' ); foreach ( $locations as $location );
									$schools = get_the_terms( $post->ID, 'school_type' ); foreach ( $schools as $school );
									$feat_img = get_the_post_thumbnail_url($post, $size, $attrs);
									$permalink = get_permalink($post->ID);
			            echo '<div>';
											echo '<div style="background-image: url('. $feat_img .');">' . get_the_title() . '</div>';
			                echo '<div>' . get_the_title() . '</div>';
											echo '<div>' . esc_html( $location->name ) . '</div>';
											echo '<div>' . esc_html( $school->name ) . '</div>';
											echo '<div>' . $address['address'] . '</div>';
											echo '<a href="' . esc_url( $permalink ) . '">Visit</a>';
			                // if the user made a search, add a column for distance
			                echo $origin ? '<div>' . round($distance, 2) . " " . $unit . " from you" . '</div>' : '' ;
			            echo '</div>';
			        }
			        echo '</div></div>';
			    } else {
			        echo '<p>No results found</p>';
			    }

			    // reset the $results_query
			    wp_reset_postdata();
			?>

			</article>

		<?php endwhile; ?>
<?php get_footer(); ?>
