<?php
/**
 * Single Job Listing Template
 * Save this file as: wp-content/plugins/your-plugin-folder/templates/single-job_listing.php
 */

get_header(); ?>

<div class="job-detail-container">
    <?php while (have_posts()) : the_post(); ?>
        <?php
        $job_id = get_the_ID();
        $company_name = get_post_meta($job_id, '_company_name', true);
        $company_logo_id = get_post_meta($job_id, '_company_logo', true);
        $company_website = get_post_meta($job_id, '_company_website', true);
        $company_facebook = get_post_meta($job_id, '_company_facebook', true);
        $company_twitter = get_post_meta($job_id, '_company_twitter', true);
        $company_linkedin = get_post_meta($job_id, '_company_linkedin', true);
        
        $salary = get_post_meta($job_id, '_job_salary', true);
        $experience = get_post_meta($job_id, '_job_experience', true);
        $deadline = get_post_meta($job_id, '_job_deadline', true);
        $remote = get_post_meta($job_id, '_job_remote', true);
        $application_url = get_post_meta($job_id, '_job_application_url', true); // Fixed meta key
        
        // Check if application deadline has passed
        $deadline_passed = false;
        $deadline_status = '';
        $deadline_message = '';
        if ($deadline) {
            $deadline_date = strtotime($deadline);
            $current_date = strtotime(date('Y-m-d'));
            if ($deadline_date < $current_date) {
                $deadline_passed = true;
                $deadline_status = 'expired';
                $deadline_message = 'Application deadline has passed';
            } elseif ($deadline_date == $current_date) {
                $deadline_status = 'today';
                $deadline_message = 'Last day to apply!';
            } elseif ($deadline_date <= strtotime('+3 days')) {
                $deadline_status = 'urgent';
                $deadline_message = 'Application closes soon - Apply now!';
            } else {
                $deadline_message = 'Applications are open';
            }
        } else {
            $deadline_message = 'No application deadline specified';
        }
        
        // Get logo URL from attachment ID
        $company_logo_url = '';
        if ($company_logo_id) {
            $company_logo_url = wp_get_attachment_image_url($company_logo_id, 'medium');
        }
        
        $categories = get_the_terms($job_id, 'job_category');
        $types = get_the_terms($job_id, 'job_type');
        $locations = get_the_terms($job_id, 'job_location');
        ?>
        
        <article class="job-detail">
            <!-- Job Header -->
            <div class="job-detail-header">
                <div class="company-info">
                    <div class="company-logo-large">
                        <?php if ($company_logo_url): ?>
                            <img src="<?php echo esc_url($company_logo_url); ?>" alt="<?php echo esc_attr($company_name); ?>" />
                        <?php else: ?>
                            <div class="logo-placeholder-large"><?php echo substr($company_name, 0, 1); ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="job-title-info">
                        <h1 class="job-title"><?php the_title(); ?></h1>
                        <p class="company-name">
                            <?php if ($company_website): ?>
                                <a href="<?php echo esc_url($company_website); ?>" target="_blank" rel="noopener"><?php echo esc_html($company_name); ?></a>
                            <?php else: ?>
                                <?php echo esc_html($company_name); ?>
                            <?php endif; ?>
                        </p>
                        
                        <!-- Job Meta -->
                        <div class="job-meta-details">
                            <?php if ($salary): ?>
                                <span class="meta-item"><strong>Salary:</strong> <?php echo esc_html($salary); ?></span>
                            <?php endif; ?>
                            <?php if ($experience): ?>
                                <span class="meta-item"><strong>Experience:</strong> <?php echo esc_html($experience); ?></span>
                            <?php endif; ?>
                            <?php if ($remote): ?>
                                <span class="meta-item"><strong>Work Type:</strong> <?php echo ucfirst($remote); ?></span>
                            <?php endif; ?>
                            <?php if ($deadline): ?>
                                <span class="meta-item <?php echo $deadline_status; ?>">
                                    <strong>Deadline:</strong> <?php echo date('M j, Y', strtotime($deadline)); ?>
                                    <?php if ($deadline_passed): ?>
                                        <span class="deadline-indicator expired">(Expired)</span>
                                    <?php elseif ($deadline_status == 'today'): ?>
                                        <span class="deadline-indicator today">(Today!)</span>
                                    <?php elseif ($deadline_status == 'urgent'): ?>
                                        <span class="deadline-indicator urgent">(Urgent)</span>
                                    <?php endif; ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Job Tags -->
                        <div class="job-tags-detail">
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
                </div>
                
                <!-- Apply Button -->
                <div class="apply-section">
                    <?php if ($deadline_passed): ?>
                        <span class="apply-button disabled">
                            Application Closed
                            <span class="deadline-icon">⏰</span>
                        </span>
                        <p class="apply-note error"><?php echo $deadline_message; ?></p>
                    <?php elseif ($application_url): ?>
                        <a href="<?php echo esc_url($application_url); ?>" target="_blank" rel="noopener" class="apply-button <?php echo $deadline_status; ?>">
                            <?php if ($deadline_status == 'today'): ?>
                                Apply Today!
                            <?php elseif ($deadline_status == 'urgent'): ?>
                                Apply Now - Urgent!
                            <?php else: ?>
                                Apply Now
                            <?php endif; ?>
                            <span class="external-link-icon">↗</span>
                        </a>
                        <p class="apply-note <?php echo $deadline_status; ?>"><?php echo $deadline_message; ?></p>
                    <?php else: ?>
                        <span class="apply-button disabled">No Application Link Available</span>
                        <p class="apply-note">Please contact the company directly</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Job Content -->
            <div class="job-detail-content">
                <div class="job-description">
                    <h2>Job Description</h2>
                    <?php the_content(); ?>
                </div>
                
                <!-- Company Social Links -->
                <?php if ($company_facebook || $company_twitter || $company_linkedin): ?>
                <div class="company-social">
                    <h3>Follow <?php echo esc_html($company_name); ?></h3>
                    <div class="social-links">
                        <?php if ($company_facebook): ?>
                            <a href="<?php echo esc_url($company_facebook); ?>" target="_blank" rel="noopener" class="social-link facebook">
                                 <i class="bi bi-facebook"></i>
                            </a>
                        <?php endif; ?>
                        <?php if ($company_twitter): ?>
                            <a href="<?php echo esc_url($company_twitter); ?>" target="_blank" rel="noopener" class="social-link twitter">
                                <i class="bi bi-twitter-x"></i>
                            </a>
                        <?php endif; ?>
                        <?php if ($company_linkedin): ?>
                            <a href="<?php echo esc_url($company_linkedin); ?>" target="_blank" rel="noopener" class="social-link linkedin">
                                  <i class="bi bi-linkedin"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Another Apply Button -->
                <div class="apply-section-bottom">
                    <?php if ($deadline_passed): ?>
                        <span class="apply-button apply-button-large disabled">
                            Application Period Ended
                            <span class="deadline-icon">⏰</span>
                        </span>
                        <p class="apply-note error">This job application deadline has passed on <?php echo date('M j, Y', strtotime($deadline)); ?></p>
                    <?php elseif ($application_url): ?>
                        <a href="<?php echo esc_url($application_url); ?>" target="_blank" rel="noopener" class="apply-button apply-button-large <?php echo $deadline_status; ?>">
                            <?php if ($deadline_status == 'today'): ?>
                                Apply Today - Last Chance!
                            <?php elseif ($deadline_status == 'urgent'): ?>
                                Apply Now - Closing Soon!
                            <?php else: ?>
                                Apply for this Position
                            <?php endif; ?>
                            <span class="external-link-icon">↗</span>
                        </a>
                        <p class="apply-note <?php echo $deadline_status; ?>">
                            <?php if ($deadline_status == 'today'): ?>
                                🚨 Final day to submit your application!
                            <?php elseif ($deadline_status == 'urgent'): ?>
                                ⚡ Application closes in a few days - Don't miss out!
                            <?php else: ?>
                                Click to apply on the company's official website
                            <?php endif; ?>
                        </p>
                    <?php else: ?>
                        <span class="apply-button apply-button-large disabled">No Application Link Available</span>
                        <p class="apply-note">Please contact the company directly to apply</p>
                    <?php endif; ?>
                </div>
            </div>
        </article>
        
    <?php endwhile; ?>
</div>

<style>
.job-detail-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
}

.job-detail-header {
    background: #fff;
    border-radius: 8px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    border: 1px solid #eee;
}

.company-info {
    display: flex;
    align-items: flex-start;
    gap: 20px;
    margin-bottom: 20px;
}

.company-logo-large img {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 8px;
    border: 2px solid #eee;
}

.logo-placeholder-large {
    width: 80px;
    height: 80px;
    background: #3498db;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
    font-weight: bold;
    border-radius: 8px;
}

.job-title-info h1 {
    margin: 0 0 10px 0;
    color: #2c3e50;
    font-size: 2em;
}

.company-name {
    margin: 0 0 15px 0;
    font-size: 1.1em;
    color: #7f8c8d;
}

.company-name a {
    color: #3498db;
    text-decoration: none;
    transition: color 0.2s;
}

.company-name a:hover {
    color: #2980b9;
    text-decoration: underline;
}

.job-meta-details {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    margin-bottom: 15px;
}

.meta-item {
    background: #f8f9fa;
    padding: 8px 12px;
    border-radius: 4px;
    font-size: 14px;
    border: 1px solid #e9ecef;
}

.job-tags-detail {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.job-tag {
    padding: 4px 10px;
    border-radius: 15px;
    font-size: 12px;
    font-weight: 500;
}

.category-tag { background: #e3f2fd; color: #1976d2; }
.type-tag { background: #f3e5f5; color: #7b1fa2; }
.location-tag { background: #e8f5e8; color: #388e3c; }

.apply-section,
.apply-section-bottom {
    text-align: center;
    margin: 20px 0;
}

.apply-button {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #27ae60;
    color: white;
    padding: 15px 30px;
    border-radius: 6px;
    text-decoration: none;
    font-size: 16px;
    font-weight: 600;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
    box-shadow: 0 2px 4px rgba(39, 174, 96, 0.2);
}

.apply-button:hover {
    background: #229954;
    color: white;
    text-decoration: none;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(39, 174, 96, 0.3);
}

.apply-button-large {
    font-size: 18px;
    padding: 18px 36px;
}

.apply-button.disabled {
    background: #95a5a6;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

.apply-button.disabled:hover {
    background: #95a5a6;
    transform: none;
    box-shadow: none;
}

.external-link-icon {
    font-size: 14px;
    opacity: 0.8;
    transition: transform 0.2s;
}

.apply-button:hover .external-link-icon {
    transform: translate(2px, -2px);
}

.apply-note {
    margin: 10px 0 0 0;
    font-size: 14px;
    color: #7f8c8d;
    font-style: italic;
}

.job-detail-content {
    background: #fff;
    border-radius: 8px;
    padding: 30px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    border: 1px solid #eee;
}

.job-description h2 {
    color: #2c3e50;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #3498db;
}

.job-description h3,
.job-description h4 {
    color: #34495e;
    margin-top: 25px;
    margin-bottom: 15px;
}

.job-description ul,
.job-description ol {
    margin-left: 20px;
    margin-bottom: 15px;
}

.job-description p {
    line-height: 1.6;
    margin-bottom: 15px;
}

.company-social {
    margin-top: 30px;
    padding-top: 30px;
    border-top: 1px solid #eee;
}

.company-social h3 {
    color: #2c3e50;
    margin-bottom: 15px;
}

/* Social Media Icons Styling */
.social-media-container {
    margin: 20px 0;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
}

.social-media-container h3 {
    margin-bottom: 15px;
    color: #333;
    font-size: 1.2em;
}

.social-icons {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}

.social-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 45px;
    height: 45px;
    border-radius: 50%;
    text-decoration: none;
    transition: all 0.3s ease;
    color: white;
    font-size: 20px;
}

.social-link:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    text-decoration: none;
}

/* Platform-specific colors */
.social-link.facebook {
    background-color: #3b5998;
}

.social-link.facebook:hover {
    background-color: #2d4373;
    color: white;
}

.social-link.twitter {
    background-color: #000000;
}

.social-link.twitter:hover {
    background-color: #333333;
    color: white;
}

.social-link.linkedin {
    background-color: #0077b5;
}

.social-link.linkedin:hover {
    background-color: #005885;
    color: white;
}

.social-link.instagram {
    background: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%);
}

.social-link.instagram:hover {
    background: linear-gradient(45deg, #e8832c 0%, #d55a35 25%, #c91f3c 50%, #b91c5f 75%, #a91681 100%);
    color: white;
}

.social-link.youtube {
    background-color: #ff0000;
}

.social-link.youtube:hover {
    background-color: #cc0000;
    color: white;
}

.social-link.email {
    background-color: #34495e;
}

.social-link.email:hover {
    background-color: #2c3e50;
    color: white;
}

.social-link.github {
    background-color: #333;
}

.social-link.github:hover {
    background-color: #000;
    color: white;
}

.social-link.whatsapp {
    background-color: #25d366;
}

.social-link.whatsapp:hover {
    background-color: #1da851;
    color: white;
}

/* Responsive design */
@media (max-width: 768px) {
    .social-icons {
        justify-content: center;
    }
    
    .social-link {
        width: 40px;
        height: 40px;
        font-size: 18px;
    }
}

/* Alternative: Rectangular buttons */
.social-icons.rectangular .social-link {
    border-radius: 6px;
    width: auto;
    padding: 8px 12px;
    min-width: 45px;
}

/* Alternative: Text with icons */
.social-icons.with-text .social-link {
    width: auto;
    padding: 8px 15px;
    border-radius: 25px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.social-icons.with-text .social-link i {
    font-size: 16px;
}

.social-icons.with-text .social-link span {
    font-size: 14px;
    font-weight: 500;
}

/* Responsive Design */
@media (max-width: 768px) {
    .job-detail-container { 
        padding: 10px; 
    }
    
    .job-detail-header,
    .job-detail-content {
        padding: 20px;
    }
    
    .company-info { 
        flex-direction: column; 
        text-align: center; 
        align-items: center;
    }
    
    .job-title-info {
        text-align: center;
    }
    
    .job-title-info h1 {
        font-size: 1.5em;
    }
    
    .job-meta-details { 
        justify-content: center; 
        flex-direction: column;
        align-items: center;
    }
    
    .job-tags-detail { 
        justify-content: center; 
    }
    
    .social-links { 
        justify-content: center; 
        flex-wrap: wrap; 
    }
    
    .apply-button {
        width: 100%;
        max-width: 300px;
        justify-content: center;
    }
    
    .apply-button-large {
        font-size: 16px;
        padding: 15px 30px;
    }
}

@media (max-width: 480px) {
    .job-meta-details {
        gap: 10px;
    }
    
    .meta-item {
        font-size: 13px;
        padding: 6px 10px;
    }
    
    .apply-button {
        font-size: 14px;
        padding: 12px 24px;
    }
    
    .apply-button-large {
        font-size: 15px;
        padding: 14px 28px;
    }
}
</style>



<?php get_footer(); ?>
