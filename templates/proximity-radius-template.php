<?php if ( get_field( 'hero_background_image' ) ): ?><?php
$heroImage = get_field('hero_background_image'); ?>
<div class="pr-hero-wrap" style="background-image: url(<?php echo esc_url($heroImage['url']); ?>);">

<?php else: // field_name returned false ?>
	<div class="pr-hero-wrap">
<?php endif; // end of if field_name logic ?>
<div class="pr-hero-overlay">
<?php get_header(); ?>

<div class="pr-hero-content">
	<div style="max-width: 831px;">
		<?php the_field('hero_content'); ?>
	</div>
</div>

</div>
</div>
		<?php wp_body_open(); ?>

		<?php while ( have_posts() ) : ?>

			<?php the_post(); ?>

			<?php
				$school_type_terms = get_terms([
				    'taxonomy' => 'school_type',
				    'hide_empty' => false,
				    'parent'   => 0
				]);
			?>

			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

				<form class="pr-form xx" method="get" action="<?php echo get_permalink(); ?>">
					<div class="pr-form-wrap">
						<div class="pr-form-item">
							<select class="minimal" name="school_type">
							<option <?php selected( $_GET['school_type'], 'all' ); ?> value="all" data-label="all">All</option>
							<?php foreach( $school_type_terms as $term ) { ?>
								<option <?php selected( $_GET['school_type'], $term->term_id ); ?> value="<?php echo $term->term_id; ?>" data-label="<?php echo $term->name; ?>"><?php echo $term->name; ?></option>
							<?php } ?>

							</select>
						</div>
					<div class="pr-form-item">
							<span class="pr-form-text">Find a School within</span>
							<input name="proximity" type="number" placeholder="0" value="<?php echo $_GET['proximity'] ?>" />
						</div>
							<div class="pr-form-item">
									<select class="minimal" name="units">
											<option value="Miles" <?php echo $_GET['units'] === 'Miles' ? 'selected' : null; ?>>Miles</option>
											<option value="K" <?php echo $_GET['units'] === 'K' ? 'selected' : null; ?>>Km</option>
									</select>
								</div>
								<div class="pr-form-item">
									<span class="pr-form-text">from</span>
							<input name="origin" type="text" placeholder="Your Address" value="<?php echo $_GET['origin'] ?>" />
						</div>
					<div class="pr-form-item">
							<input class="pr-submit" type="submit" value="Search" />
							<span class="pr-form-text"><a href="<?php echo get_permalink(); ?>">Reset</a></span>
					</div>
					</div>
			</form>
			</article>

		<?php endwhile; ?>
			<?php

			wp_reset_postdata();

			// get the parameters from the URL
			// these parameter names come from the 'name' attribute of each input in the form
			$proximity = isset($_GET['proximity']) ? $_GET['proximity'] : null;
			$origin = isset($_GET['origin']) ? $_GET['origin'] : null;
			$unit = isset($_GET['units']) ? $_GET['units'] : null;
			$type = !empty($_GET['school_type']) ? $_GET['school_type'] : 'all';

			    // create an empty array to store results for a later query
			    $results = array();

			    // only run this query if a user has made a search
			    if ($origin) {

			    	$proximity_args = array(
			                'post_type' 		=> 'schools', /* this is the name of our custom post type */
			                'posts_per_page'	=> -1
			        );

					$proximity_query = new WP_Query($proximity_args);


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
			            //'posts_per_page' 	=> -1
			        );
			    }

			    if( $type != 'all' ){

					$results_args['tax_query'] = array(
						array(
                        'taxonomy' => 'school_type',
                        'field' => 'term_id',
                        'terms' => array($type),
                        'operator' => 'IN',
                   		)
					);
				}

				$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
				$results_args['paged'] = $paged;
				$results_args['posts_per_page'] = 3;


			    // create a new query to display the results
			    $results_query = new WP_Query($results_args);
			    if($results_query->have_posts()) {
			        // if the user made a search, add a column for distance
			        echo $origin ? '<ul class="proximity-wrap">' : '<ul class="proximity-wrap">';
			        while($results_query->have_posts()) {
			            $results_query->the_post();
			            $address = get_field('address');
			            $distance = YOUR_THEME_NAME_get_distance($origin, $address['lat'], $address['lng'], $unit);
									$locations = get_the_terms( $post->ID, 'location' );
									foreach ( $locations as $location );
									$schools = get_the_terms( $post->ID, 'school_type' );
									foreach ( $schools as $school );
									$feat_img = get_the_post_thumbnail_url($post, $size, $attrs);
									$permalink = get_permalink($post->ID);

			            echo '<li class="proximity-item"><div class="proximity-card">';
											echo '<div class="proximity-img" style="background-image: url('. $feat_img .');"></div>';
											echo '<div class="proximity-type"><a href="'.get_term_link($school).'">#' . esc_html( $school->name ) . '</a></div>';
											echo '<div class="proximity-content"><h2 class="proximity-title"><a href="' . esc_url( $permalink ) . '">' . get_the_title() . '</a></h2>';
											echo '<div class="pr-address">' . $address['address'] . '</div>';
											echo '<span class="proximity-location">' . esc_html( $location->name ) . '</span>';
											?>
											<?php if ( get_field( 'website' ) ): ?><?php
											echo '<a class="proximity-link pr-visit" href="' . get_field('website') . '" target="_blank">Visit</a>';
											?>
											<?php else: // field_name returned false ?><?php
												echo '<a class="proximity-link" href="' . esc_url( $permalink ) . '">More Info</a>';
											?>
											<?php endif; // end of if field_name logic ?>
											<?php
			                // if the user made a search, add a column for distance
			                echo $origin ? '<div>' . round($distance, 2) . " " . $unit . " from you" . '</div>' : '' ;
			            echo '</div></div></li>';
			        }
			        echo '</ul></ul>';

			        wp_reset_postdata();

			         echo '<div class="pagination">';

			         echo paginate_links( array(
			             'base'         => str_replace( 999999999, '%#%', esc_url( get_pagenum_link( 999999999 ) ) ),
			             'total'        => $results_query->max_num_pages,
			             'current'      => max( 1, get_query_var( 'paged' ) ),
			             'format'       => '?paged=%#%',
			             'type'         => 'plain',
			             'end_size'     => 2,
			             'mid_size'     => 1,
			             'prev_next'    => true,
			             'prev_text'    => sprintf( '<i></i> %1$s', __( 'Previous', 'text-domain' ) ),
			             'next_text'    => sprintf( '%1$s <i></i>', __( 'Next', 'text-domain' ) ),
			             'add_args'     => false,
			             'add_fragment' => '',
			         ) );

				 echo '</div>';
			    } else {
			        echo '<p>No results found</p>';
			    }

			    // reset the $results_query

			?>


<?php get_footer(); ?>
