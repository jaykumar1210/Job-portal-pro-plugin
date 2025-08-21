<?php

/**

 * Plugin Name: Job Portal Pro
 * Plugin URI: https://jay-kumar-projects-qhi05ib.gamma.site/
 * Description: A comprehensive job portal plugin for listing jobs with company details, filters, and external application links.
 * Version: 1.1.0
 * Author: Jay Kumar
 */


// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('JOB_PORTAL_PLUGIN_URL', plugin_dir_url(__FILE__));
define('JOB_PORTAL_PLUGIN_PATH', plugin_dir_path(__FILE__));

class JobPortalPlugin {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('add_meta_boxes', array($this, 'add_job_meta_boxes'));
        add_action('save_post', array($this, 'save_job_meta'));
        add_shortcode('job_listings', array($this, 'job_listings_shortcode'));
        add_shortcode('job_filters', array($this, 'job_filters_shortcode'));
        add_action('wp_ajax_filter_jobs', array($this, 'filter_jobs_ajax'));
        add_action('wp_ajax_nopriv_filter_jobs', array($this, 'filter_jobs_ajax'));
        add_filter('single_template', array($this, 'load_single_job_template'));
    }
    
    public function init() {
        $this->create_job_post_type();
        $this->create_job_taxonomies();
    }
    
    public function create_job_post_type() {
        $labels = array(
            'name' => 'Jobs',
            'singular_name' => 'Job',
            'menu_name' => 'Job Portal',
            'add_new' => 'Add New Job',
            'add_new_item' => 'Add New Job',
            'edit_item' => 'Edit Job',
            'new_item' => 'New Job',
            'view_item' => 'View Job',
            'search_items' => 'Search Jobs',
            'not_found' => 'No jobs found',
            'not_found_in_trash' => 'No jobs found in Trash'
        );
        
        $args = array(
            'labels' => $labels,
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'job'),
            'capability_type' => 'post',
            'has_archive' => true,
            'hierarchical' => false,
            'menu_position' => 5,
            'menu_icon' => 'dashicons-businessman',
            'supports' => array('title', 'editor', 'thumbnail')
        );
        
        register_post_type('job_listing', $args);
    }
    
    public function create_job_taxonomies() {
        // Job Categories
        register_taxonomy('job_category', 'job_listing', array(
            'hierarchical' => true,
            'labels' => array(
                'name' => 'Job Categories',
                'singular_name' => 'Job Category',
                'search_items' => 'Search Categories',
                'all_items' => 'All Categories',
                'edit_item' => 'Edit Category',
                'update_item' => 'Update Category',
                'add_new_item' => 'Add New Category',
                'new_item_name' => 'New Category Name',
                'menu_name' => 'Categories',
            ),
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'job-category'),
        ));
        
        // Job Types
        register_taxonomy('job_type', 'job_listing', array(
            'hierarchical' => false,
            'labels' => array(
                'name' => 'Job Types',
                'singular_name' => 'Job Type',
                'search_items' => 'Search Types',
                'all_items' => 'All Types',
                'edit_item' => 'Edit Type',
                'update_item' => 'Update Type',
                'add_new_item' => 'Add New Type',
                'new_item_name' => 'New Type Name',
                'menu_name' => 'Job Types',
            ),
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'job-type'),
        ));
        
        // Locations
        register_taxonomy('job_location', 'job_listing', array(
            'hierarchical' => true,
            'labels' => array(
                'name' => 'Locations',
                'singular_name' => 'Location',
                'search_items' => 'Search Locations',
                'all_items' => 'All Locations',
                'edit_item' => 'Edit Location',
                'update_item' => 'Update Location',
                'add_new_item' => 'Add New Location',
                'new_item_name' => 'New Location Name',
                'menu_name' => 'Locations',
            ),
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'job-location'),
        ));
    }
    
    public function add_job_meta_boxes() {
        add_meta_box(
            'job_details',
            'Job Details',
            array($this, 'job_details_meta_box'),
            'job_listing',
            'normal',
            'high'
        );
        
        add_meta_box(
            'company_details',
            'Company Details',
            array($this, 'company_details_meta_box'),
            'job_listing',
            'normal',
            'high'
        );
    }
    
    public function job_details_meta_box($post) {
        wp_nonce_field('job_details_nonce', 'job_details_nonce');
        
        $salary = get_post_meta($post->ID, '_job_salary', true);
        $experience = get_post_meta($post->ID, '_job_experience', true);
        $deadline = get_post_meta($post->ID, '_job_deadline', true);
        $remote = get_post_meta($post->ID, '_job_remote', true);
        $application_url = get_post_meta($post->ID, '_job_application_url', true);
        
        echo '<table class="form-table">';
        echo '<tr><th><label for="job_salary">Salary Range</label></th>';
        echo '<td><input type="text" id="job_salary" name="job_salary" value="' . esc_attr($salary) . '" placeholder="e.g. $50,000 - $70,000" style="width: 100%;" /></td></tr>';
        
        echo '<tr><th><label for="job_experience">Experience Required</label></th>';
        echo '<td><input type="text" id="job_experience" name="job_experience" value="' . esc_attr($experience) . '" placeholder="e.g. 2-5 years" style="width: 100%;" /></td></tr>';
        
        echo '<tr><th><label for="job_deadline">Application Deadline</label></th>';
        echo '<td><input type="date" id="job_deadline" name="job_deadline" value="' . esc_attr($deadline) . '" style="width: 100%;" /></td></tr>';
        
        echo '<tr><th><label for="job_remote">Remote Work</label></th>';
        echo '<td><select id="job_remote" name="job_remote" style="width: 100%;">';
        echo '<option value="">Select...</option>';
        echo '<option value="remote"' . selected($remote, 'yes', false) . '>Remote</option>';
        echo '<option value="on-site"' . selected($remote, 'no', false) . '>On-Site</option>';
        echo '<option value="hybrid"' . selected($remote, 'hybrid', false) . '>Hybrid</option>';
        echo '</select></td></tr>';
        
        echo '<tr><th><label for="job_application_url">Application URL</label></th>';
        echo '<td><input type="url" id="job_application_url" name="job_application_url" value="' . esc_attr($application_url) . '" placeholder="https://company.com/apply/job-123" style="width: 100%;" required />
        <p class="description">Enter the external URL where candidates should apply for this job. This is required.</p></td></tr>';
        
        echo '</table>';
    }
    
    public function company_details_meta_box($post) {
        wp_nonce_field('company_details_nonce', 'company_details_nonce');
        
        $company_name = get_post_meta($post->ID, '_company_name', true);
        $company_website = get_post_meta($post->ID, '_company_website', true);
        $company_logo = get_post_meta($post->ID, '_company_logo', true);
        $company_facebook = get_post_meta($post->ID, '_company_facebook', true);
        $company_twitter = get_post_meta($post->ID, '_company_twitter', true);
        $company_linkedin = get_post_meta($post->ID, '_company_linkedin', true);
        
        // Enqueue media uploader scripts
        wp_enqueue_media();
        
        echo '<table class="form-table">';
        echo '<tr><th><label for="company_name">Company Name</label></th>';
        echo '<td><input type="text" id="company_name" name="company_name" value="' . esc_attr($company_name) . '" style="width: 100%;" required /></td></tr>';
        
        echo '<tr><th><label for="company_website">Company Website</label></th>';
        echo '<td><input type="url" id="company_website" name="company_website" value="' . esc_attr($company_website) . '" style="width: 100%;" /></td></tr>';
        
        echo '<tr><th><label for="company_logo">Company Logo</label></th>';
        echo '<td>';
        echo '<input type="hidden" id="company_logo" name="company_logo" value="' . esc_attr($company_logo) . '" />';
        echo '<div id="company-logo-preview" style="margin-bottom: 10px;">';
        if ($company_logo) {
            $logo_url = wp_get_attachment_image_url($company_logo, 'thumbnail');
            if ($logo_url) {
                echo '<img src="' . esc_url($logo_url) . '" style="max-width: 150px; max-height: 150px; border: 1px solid #ddd; padding: 5px;" />';
            }
        }
        echo '</div>';
        echo '<button type="button" class="button" id="upload-company-logo">Upload Logo</button>';
        echo '<button type="button" class="button" id="remove-company-logo" style="margin-left: 10px;"' . ($company_logo ? '' : ' style="display:none;"') . '>Remove Logo</button>';
        echo '<p class="description">Upload a company logo from your media library</p>';
        echo '</td></tr>';
        
        echo '<tr><th><label for="company_facebook">Facebook</label></th>';
        echo '<td><input type="url" id="company_facebook" name="company_facebook" value="' . esc_attr($company_facebook) . '" style="width: 100%;" /></td></tr>';
        
        echo '<tr><th><label for="company_twitter">Twitter</label></th>';
        echo '<td><input type="url" id="company_twitter" name="company_twitter" value="' . esc_attr($company_twitter) . '" style="width: 100%;" /></td></tr>';
        
        echo '<tr><th><label for="company_linkedin">LinkedIn</label></th>';
        echo '<td><input type="url" id="company_linkedin" name="company_linkedin" value="' . esc_attr($company_linkedin) . '" style="width: 100%;" /></td></tr>';
        echo '</table>';
        
        // Add JavaScript for media uploader
        ?>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            var mediaUploader;
            
            $('#upload-company-logo').click(function(e) {
                e.preventDefault();
                
                if (mediaUploader) {
                    mediaUploader.open();
                    return;
                }
                
                mediaUploader = wp.media({
                    title: 'Choose Company Logo',
                    button: {
                        text: 'Use this image'
                    },
                    multiple: false,
                    library: {
                        type: 'image'
                    }
                });
                
                mediaUploader.on('select', function() {
                    var attachment = mediaUploader.state().get('selection').first().toJSON();
                    $('#company_logo').val(attachment.id);
                    $('#company-logo-preview').html('<img src="' + attachment.sizes.thumbnail.url + '" style="max-width: 150px; max-height: 150px; border: 1px solid #ddd; padding: 5px;" />');
                    $('#remove-company-logo').show();
                });
                
                mediaUploader.open();
            });
            
            $('#remove-company-logo').click(function(e) {
                e.preventDefault();
                $('#company_logo').val('');
                $('#company-logo-preview').html('');
                $(this).hide();
            });
        });
        </script>
        <?php
    }
    
    public function save_job_meta($post_id) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!current_user_can('edit_post', $post_id)) return;
        if (get_post_type($post_id) !== 'job_listing') return;
        
        // Save job details
        if (isset($_POST['job_details_nonce']) && wp_verify_nonce($_POST['job_details_nonce'], 'job_details_nonce')) {
            update_post_meta($post_id, '_job_salary', sanitize_text_field($_POST['job_salary']));
            update_post_meta($post_id, '_job_experience', sanitize_text_field($_POST['job_experience']));
            update_post_meta($post_id, '_job_deadline', sanitize_text_field($_POST['job_deadline']));
            update_post_meta($post_id, '_job_remote', sanitize_text_field($_POST['job_remote']));
            update_post_meta($post_id, '_job_application_url', esc_url_raw($_POST['job_application_url']));
        }
        
        // Save company details
        if (isset($_POST['company_details_nonce']) && wp_verify_nonce($_POST['company_details_nonce'], 'company_details_nonce')) {
            update_post_meta($post_id, '_company_name', sanitize_text_field($_POST['company_name']));
            update_post_meta($post_id, '_company_website', esc_url_raw($_POST['company_website']));
            update_post_meta($post_id, '_company_logo', intval($_POST['company_logo']));
            update_post_meta($post_id, '_company_facebook', esc_url_raw($_POST['company_facebook']));
            update_post_meta($post_id, '_company_twitter', esc_url_raw($_POST['company_twitter']));
            update_post_meta($post_id, '_company_linkedin', esc_url_raw($_POST['company_linkedin']));
        }
    }
    
    public function enqueue_scripts() {
        wp_enqueue_style('job-portal-style', JOB_PORTAL_PLUGIN_URL . 'assets/style.css');
        wp_enqueue_script('job-portal-script', JOB_PORTAL_PLUGIN_URL . 'assets/script.js', array('jquery'), '1.0', true);
        wp_localize_script('job-portal-script', 'job_portal_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('job_portal_nonce')
        ));
    }
    
    public function job_filters_shortcode($atts) {
        $categories = get_terms(array('taxonomy' => 'job_category', 'hide_empty' => false));
        $types = get_terms(array('taxonomy' => 'job_type', 'hide_empty' => false));
        $locations = get_terms(array('taxonomy' => 'job_location', 'hide_empty' => false));
        
        ob_start();
        ?>
        <div class="job-filters">
            <form id="job-filter-form">
                <div class="filter-row">
                    <div class="filter-col">
                        <input type="text" id="job-search" placeholder="Search jobs..." />
                    </div>
                    <div class="filter-col">
                        <select id="job-category-filter">
                            <option value="">All Categories</option>
                            <?php foreach($categories as $category): ?>
                                <option value="<?php echo $category->slug; ?>"><?php echo $category->name; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-col">
                        <select id="job-type-filter">
                            <option value="">All Types</option>
                            <?php foreach($types as $type): ?>
                                <option value="<?php echo $type->slug; ?>"><?php echo $type->name; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-col">
                        <select id="job-location-filter">
                            <option value="">All Locations</option>
                            <?php foreach($locations as $location): ?>
                                <option value="<?php echo $location->slug; ?>"><?php echo $location->name; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-col">
                        <select id="job-remote-filter">
                            <option value="">Remote Options</option>
                            <option value="remote">Remote</option>
                            <option value="on-site">On-site</option>
                            <option value="hybrid">Hybrid</option>
                        </select>
                    </div>
                </div>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }
    
    public function job_listings_shortcode($atts) {
        $atts = shortcode_atts(array(
            'posts_per_page' => 10,
            'category' => '',
            'type' => '',
            'location' => ''
        ), $atts);
        
        $args = array(
            'post_type' => 'job_listing',
            'post_status' => 'publish',
            'posts_per_page' => $atts['posts_per_page'],
            'meta_query' => array(
                'relation' => 'OR',
                array(
                    'key' => '_job_deadline',
                    'value' => date('Y-m-d'),
                    'compare' => '>=',
                    'type' => 'DATE'
                ),
                array(
                    'key' => '_job_deadline',
                    'compare' => 'NOT EXISTS'
                )
            )
        );
        
        // Add taxonomy queries if specified
        $tax_query = array();
        if (!empty($atts['category'])) {
            $tax_query[] = array(
                'taxonomy' => 'job_category',
                'field' => 'slug',
                'terms' => $atts['category']
            );
        }
        if (!empty($atts['type'])) {
            $tax_query[] = array(
                'taxonomy' => 'job_type',
                'field' => 'slug',
                'terms' => $atts['type']
            );
        }
        if (!empty($atts['location'])) {
            $tax_query[] = array(
                'taxonomy' => 'job_location',
                'field' => 'slug',
                'terms' => $atts['location']
            );
        }
        if (!empty($tax_query)) {
            $args['tax_query'] = $tax_query;
        }
        
        $jobs = new WP_Query($args);
        
        ob_start();
        ?>
        <div id="job-listings" class="job-listings">
            <?php if ($jobs->have_posts()): ?>
                <?php while ($jobs->have_posts()): $jobs->the_post(); ?>
                    <?php $this->render_job_card(get_the_ID()); ?>
                <?php endwhile; ?>
                <?php wp_reset_postdata(); ?>
            <?php else: ?>
                <p class="no-jobs">No jobs found matching your criteria.</p>
            <?php endif; ?>
        </div>
        <div id="job-loading" class="job-loading" style="display: none;">Loading...</div>
        <?php
        return ob_get_clean();
    }
    
    private function render_job_card($job_id) {
        $company_name = get_post_meta($job_id, '_company_name', true);
        $company_logo_id = get_post_meta($job_id, '_company_logo', true);
        $company_website = get_post_meta($job_id, '_company_website', true);
        $salary = get_post_meta($job_id, '_job_salary', true);
        $experience = get_post_meta($job_id, '_job_experience', true);
        $deadline = get_post_meta($job_id, '_job_deadline', true);
        $remote = get_post_meta($job_id, '_job_remote', true);
        $application_url = get_post_meta($job_id, '_job_application_url', true);
        
        // Check if application deadline has passed
        $deadline_passed = false;
        $deadline_status = '';
        if ($deadline) {
            $deadline_date = strtotime($deadline);
            $current_date = strtotime(date('Y-m-d'));
            if ($deadline_date < $current_date) {
                $deadline_passed = true;
                $deadline_status = 'expired';
            } elseif ($deadline_date == $current_date) {
                $deadline_status = 'today';
            } elseif ($deadline_date <= strtotime('+3 days')) {
                $deadline_status = 'urgent';
            }
        }
        
        // Get logo URL from attachment ID
        $company_logo_url = '';
        if ($company_logo_id) {
            $company_logo_url = wp_get_attachment_image_url($company_logo_id, 'thumbnail');
        }
        
        $categories = get_the_terms($job_id, 'job_category');
        $types = get_the_terms($job_id, 'job_type');
        $locations = get_the_terms($job_id, 'job_location');
        ?>
        <div class="job-card <?php echo $deadline_status; ?>" data-job-id="<?php echo $job_id; ?>">
            <div class="job-header">
                <div class="company-logo">
                    <?php if ($company_logo_url): ?>
                        <img src="<?php echo esc_url($company_logo_url); ?>" alt="<?php echo esc_attr($company_name); ?>" />
                    <?php else: ?>
                        <div class="logo-placeholder"><?php echo substr($company_name, 0, 1); ?></div>
                    <?php endif; ?>
                </div>
                <div class="job-title-company">
                    <h3 class="job-title"><?php echo get_the_title($job_id); ?></h3>
                    <p class="company-name">
                        <?php if ($company_website): ?>
                            <a href="<?php echo esc_url($company_website); ?>" target="_blank"><?php echo esc_html($company_name); ?></a>
                        <?php else: ?>
                            <?php echo esc_html($company_name); ?>
                        <?php endif; ?>
                    </p>
                </div>
                <div class="job-meta">
                    <?php if ($salary): ?>
                        <span class="job-salary"><?php echo esc_html($salary); ?></span>
                    <?php endif; ?>
                    <?php if ($remote): ?>
                        <span class="job-remote remote-<?php echo esc_attr($remote); ?>"><?php echo ucfirst($remote); ?></span>
                    <?php endif; ?>
                    <?php if ($deadline_passed): ?>
                        <span class="deadline-status expired">Application Closed</span>
                    <?php elseif ($deadline_status == 'today'): ?>
                        <span class="deadline-status today">Last Day!</span>
                    <?php elseif ($deadline_status == 'urgent'): ?>
                        <span class="deadline-status urgent">Closing Soon</span>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="job-content">
                <div class="job-excerpt">
                    <?php echo wp_trim_words(get_the_excerpt($job_id), 30); ?>
                </div>
                
                <div class="job-tags">
                    <?php if ($categories): ?>
                        <?php foreach ($categories as $category): ?>
                            <span class="job-tag category-tag"><?php echo $category->name; ?></span>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <?php if ($types): ?>
                        <?php foreach ($types as $type): ?>
                            <span class="job-tag type-tag"><?php echo $type->name; ?></span>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <?php if ($locations): ?>
                        <?php foreach ($locations as $location): ?>
                            <span class="job-tag location-tag"><?php echo $location->name; ?></span>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="job-footer">
                <div class="job-details">
                    <?php if ($experience): ?>
                        <span class="job-experience">Experience: <?php echo esc_html($experience); ?></span>
                    <?php endif; ?>
                    <?php if ($deadline): ?>
                        <span class="job-deadline <?php echo $deadline_status; ?>">
                            Deadline: <?php echo date('M j, Y', strtotime($deadline)); ?>
                            <?php if ($deadline_passed): ?>
                                <strong>(Expired)</strong>
                            <?php elseif ($deadline_status == 'today'): ?>
                                <strong>(Today!)</strong>
                            <?php elseif ($deadline_status == 'urgent'): ?>
                                <strong>(Urgent)</strong>
                            <?php endif; ?>
                        </span>
                    <?php endif; ?>
                </div>
                <div class="job-actions">
                    <?php if ($deadline_passed): ?>
                        <span class="btn-apply-disabled">Application Closed</span>
                    <?php elseif ($application_url): ?>
                        <a href="<?php echo esc_url($application_url); ?>" target="_blank" rel="noopener" class="btn-apply-external">Apply Now</a>
                    <?php else: ?>
                        <span class="btn-apply-disabled">No Application URL</span>
                    <?php endif; ?>
                    <a href="<?php echo get_permalink($job_id); ?>" class="btn-view">View Details</a>
                </div>
            </div>
        </div>
        <?php
    }
    
    public function filter_jobs_ajax() {
        check_ajax_referer('job_portal_nonce', 'nonce');
        
        $search = sanitize_text_field($_POST['search'] ?? '');
        $category = sanitize_text_field($_POST['category'] ?? '');
        $type = sanitize_text_field($_POST['type'] ?? '');
        $location = sanitize_text_field($_POST['location'] ?? '');
        $remote = sanitize_text_field($_POST['remote'] ?? '');
        
        $args = array(
            'post_type' => 'job_listing',
            'post_status' => 'publish',
            'posts_per_page' => 10,
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'relation' => 'OR',
                    array(
                        'key' => '_job_deadline',
                        'value' => date('Y-m-d'),
                        'compare' => '>=',
                        'type' => 'DATE'
                    ),
                    array(
                        'key' => '_job_deadline',
                        'compare' => 'NOT EXISTS'
                    )
                )
            )
        );
        
        if (!empty($search)) {
            $args['s'] = $search;
        }
        
        if (!empty($remote)) {
            $args['meta_query'][] = array(
                'key' => '_job_remote',
                'value' => $remote,
                'compare' => '='
            );
        }
        
        $tax_query = array();
        if (!empty($category)) {
            $tax_query[] = array(
                'taxonomy' => 'job_category',
                'field' => 'slug',
                'terms' => $category
            );
        }
        if (!empty($type)) {
            $tax_query[] = array(
                'taxonomy' => 'job_type',
                'field' => 'slug',
                'terms' => $type
            );
        }
        if (!empty($location)) {
            $tax_query[] = array(
                'taxonomy' => 'job_location',
                'field' => 'slug',
                'terms' => $location
            );
        }
        if (!empty($tax_query)) {
            $args['tax_query'] = $tax_query;
        }
        
        $jobs = new WP_Query($args);
        
        ob_start();
        if ($jobs->have_posts()) {
            while ($jobs->have_posts()) {
                $jobs->the_post();
                $this->render_job_card(get_the_ID());
            }
            wp_reset_postdata();
        } else {
            echo '<p class="no-jobs">No jobs found matching your criteria.</p>';
        }
        
        wp_die(ob_get_clean());
    }
    
    public function load_single_job_template($template) {
        if (is_singular('job_listing')) {
            $plugin_template = JOB_PORTAL_PLUGIN_PATH . 'templates/single-job_listing.php';
            if (file_exists($plugin_template)) {
                return $plugin_template;
            }
            // Fallback to theme template if plugin template doesn't exist
            $theme_template = locate_template('single-job_listing.php');
            if ($theme_template) {
                return $theme_template;
            }
        }
        return $template;
    }
}

// Initialize the plugin
new JobPortalPlugin();

// Activation hook
register_activation_hook(__FILE__, 'job_portal_activate');
function job_portal_activate() {
    // Create custom post type and taxonomies
    $plugin = new JobPortalPlugin();
    $plugin->create_job_post_type();
    $plugin->create_job_taxonomies();
    
    // Flush rewrite rules
    flush_rewrite_rules();
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'job_portal_deactivate');
function job_portal_deactivate() {
    flush_rewrite_rules();
}
?>