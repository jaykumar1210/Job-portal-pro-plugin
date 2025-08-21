jQuery(document).ready(function($) {
    // Filter functionality
    let filterTimeout;
    const $filterForm = $('#job-filter-form');
    const $jobListings = $('#job-listings');
    const $loadingDiv = $('#job-loading');
    // Filter inputs
    const $searchInput = $('#job-search');
    const $categoryFilter = $('#job-category-filter');
    const $typeFilter = $('#job-type-filter');
    const $locationFilter = $('#job-location-filter');
    const $remoteFilter = $('#job-remote-filter');
    // Bind filter events
    $searchInput.on('input', debounceFilter);
    $categoryFilter.on('change', filterJobs);
    $typeFilter.on('change', filterJobs);
    $locationFilter.on('change', filterJobs);
    $remoteFilter.on('change', filterJobs);

    function debounceFilter() {
        clearTimeout(filterTimeout);
        filterTimeout = setTimeout(filterJobs, 300);
    }

    function filterJobs() {
        const filterData = {
            action: 'filter_jobs',
            nonce: job_portal_ajax.nonce,
            search: $searchInput.val(),



            category: $categoryFilter.val(),



            type: $typeFilter.val(),



            location: $locationFilter.val(),



            remote: $remoteFilter.val()



        };



        



        $loadingDiv.show();



        $jobListings.animate({ opacity: 0.5 }, 200);



        



        $.post(job_portal_ajax.ajax_url, filterData)



            .done(function(response) {



                $jobListings.html(response).animate({ opacity: 1 }, 200);



                $loadingDiv.hide();



            })



            .fail(function() {



                $jobListings.html('<p class="no-jobs">Error loading jobs. Please try again.</p>').animate({ opacity: 1 }, 200);



                $loadingDiv.hide();



            });



    }



    



    // Application modal functionality



    let currentJobId = null;



    



    // Create modal HTML if it doesn't exist



    // if (!$('#job-application-modal').length) {



    //     const modalHtml = `



    //         <div id="job-application-modal" class="job-modal-overlay">



    //             <div class="job-modal">



    //                 <div class="job-modal-header">



    //                     <h3 class="job-modal-title">Apply for Position</h3>



    //                     <button class="job-modal-close" type="button">&times;</button>



    //                 </div>



    //                 <div class="job-modal-body">



    //                     <div id="application-messages"></div>



    //                     <form id="job-application-form">



    //                         <div class="form-group">



    //                             <label for="applicant-name">Full Name *</label>



    //                             <input type="text" id="applicant-name" name="applicant_name" required>



    //                         </div>



    //                         <div class="form-group">



    //                             <label for="applicant-email">Email Address *</label>



    //                             <input type="email" id="applicant-email" name="applicant_email" required>



    //                         </div>



    //                         <div class="form-group">



    //                             <label for="applicant-phone">Phone Number</label>



    //                             <input type="tel" id="applicant-phone" name="applicant_phone">



    //                         </div>



    //                         <div class="form-group">



    //                             <label for="cover-letter">Cover Letter *</label>



    //                             <textarea id="cover-letter" name="cover_letter" placeholder="Tell us why you're perfect for this role..." required></textarea>



    //                         </div>



    //                         <div class="form-group">



    //                       <input type="file" name="resume" accept=".pdf,.doc,.docx" required>

    //                       <input type="hidden" name="job_id" value="123">

    //                         </div>



    //                         <div class="form-actions">



    //                             <button type="button" class="btn-cancel">Cancel</button>



    //                             <button type="submit" class="btn-submit">Submit Application</button>



    //                         </div>



    //                     </form>



    //                 </div>



    //             </div>



    //         </div>



    //     `;



    //     $('body').append(modalHtml);



    // }



    



    const $modal = $('#job-application-modal');



    const $applicationForm = $('#job-application-form');



    const $messagesDiv = $('#application-messages');



    



    // Open modal when apply button is clicked



    $(document).on('click', '.btn-apply', function(e) {



        e.preventDefault();



        currentJobId = $(this).data('job-id');



        const jobTitle = $(this).closest('.job-card').find('.job-title').text();



        const companyName = $(this).closest('.job-card').find('.company-name').text();



        



        $('.job-modal-title').text(`Apply for ${jobTitle} at ${companyName}`);



        $messagesDiv.empty();



        $applicationForm[0].reset();



        $modal.addClass('active');



        $('body').addClass('modal-open');



        



        // Focus on first input



        setTimeout(() => {



            $('#applicant-name').focus();



        }, 300);



    });



    



    // Close modal



    function closeModal() {



        $modal.removeClass('active');



        $('body').removeClass('modal-open');



        currentJobId = null;



    }



    



    $('.job-modal-close, .btn-cancel').on('click', closeModal);



    



    // Close modal when clicking outside



    $modal.on('click', function(e) {



        if (e.target === this) {



            closeModal();



        }



    });



    



    // Close modal with escape key



    $(document).on('keydown', function(e) {



        if (e.keyCode === 27 && $modal.hasClass('active')) {



            closeModal();



        }



    });



    

// Handle form submission with file upload

$applicationForm.on('submit', function(e) {

    e.preventDefault();



    if (!currentJobId) {

        showMessage('error', 'Invalid job selection. Please try again.');

        return;

    }



    const formData = new FormData(this); // gets all fields including file

    formData.append('action', 'apply_job');

    formData.append('nonce', job_portal_ajax.nonce);

    formData.append('job_id', currentJobId);



    // Basic validation

    if (!formData.get('applicant_name') || !formData.get('applicant_email') || !formData.get('cover_letter')) {

        showMessage('error', 'Please fill in all required fields.');

        return;

    }



    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (!emailRegex.test(formData.get('applicant_email'))) {

        showMessage('error', 'Please enter a valid email address.');

        return;

    }



    const $submitBtn = $('.btn-submit');

    const originalText = $submitBtn.text();

    $submitBtn.text('Submitting...').prop('disabled', true);



    $.ajax({

        url: job_portal_ajax.ajax_url,

        type: 'POST',

        data: formData,

        processData: false,

        contentType: false,

        success: function(response) {

            try {

                const result = typeof response === 'string' ? JSON.parse(response) : response;

                if (result.success) {

                    showMessage('success', result.message);

                    $applicationForm[0].reset();

                    setTimeout(closeModal, 3000);

                } else {

                    showMessage('error', result.message);

                }

            } catch (e) {

                showMessage('error', 'Invalid response from server. Please try again.');

            }

        },

        error: function(xhr) {

            let errorMessage = 'Failed to submit application. Please try again.';

            if (xhr.responseText) {

                try {

                    const result = JSON.parse(xhr.responseText);

                    errorMessage = result.message || errorMessage;

                } catch (e) {}

            }

            showMessage('error', errorMessage);

        },

        complete: function() {

            $submitBtn.text(originalText).prop('disabled', false);

        }

    });

});





    



    function showMessage(type, message) {



        const messageClass = type === 'success' ? 'success' : 'error';



        const messageHtml = `<div class="job-message ${messageClass}">${message}</div>`;



        $messagesDiv.html(messageHtml);



        



        // Scroll to message



        $messagesDiv[0].scrollIntoView({ behavior: 'smooth', block: 'nearest' });



    }



    



    // Add loading states for better UX



    $(document).ajaxStart(function() {



        $('body').addClass('ajax-loading');



    }).ajaxStop(function() {



        $('body').removeClass('ajax-loading');



    });



    



    // Smooth scroll to job listings when filters change



    function scrollToResults() {



        if ($jobListings.length) {



            $('html, body').animate({



                scrollTop: $jobListings.offset().top - 20



            }, 300);



        }



    }



    



    // Add scroll to results after filtering



    const originalFilterJobs = filterJobs;



    filterJobs = function() {



        originalFilterJobs();



        setTimeout(scrollToResults, 500);



    };



    



    // Auto-resize textareas



    $('textarea').on('input', function() {



        this.style.height = 'auto';



        this.style.height = Math.min(this.scrollHeight, 300) + 'px';



    });



    



    // Form validation styling



    $('input[required], textarea[required]').on('blur', function() {



        const $this = $(this);



        if (!$this.val().trim()) {



            $this.addClass('error');



        } else {



            $this.removeClass('error');



        }



    });



    



    $('input[type="email"]').on('blur', function() {



        const $this = $(this);



        const email = $this.val().trim();



        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;



        



        if (email && !emailRegex.test(email)) {



            $this.addClass('error');



        } else {



            $this.removeClass('error');



        }



    });



    



    // Remove error styling on input



    $('input, textarea').on('input', function() {



        $(this).removeClass('error');



    });



    



    // Prevent modal from closing when clicking on modal content



    $('.job-modal').on('click', function(e) {



        e.stopPropagation();



    });



    



    // Initialize tooltips if needed (can be extended)



    $('[data-tooltip]').hover(



        function() {



            const tooltip = $(this).data('tooltip');



            $(this).append(`<div class="tooltip">${tooltip}</div>`);



        },



        function() {



            $('.tooltip').remove();



        }



    );



    



    // Add fade-in animation for job cards



    function animateJobCards() {



        $('.job-card').each(function(index) {



            $(this).css('opacity', '0').delay(index * 100).animate({ opacity: 1 }, 300);



        });



    }



    



    // Run animation on page load



    animateJobCards();



    



    // Re-run animation after filtering



    const originalDone = $.post;



    $(document).ajaxComplete(function(event, xhr, settings) {



        if (settings.data && settings.data.includes('action=filter_jobs')) {



            setTimeout(animateJobCards, 100);



        }



    });



});







// Add CSS for better form validation



const additionalCSS = `



<style>



.ajax-loading {



    cursor: wait;



}







.form-group input.error,



.form-group textarea.error {



    border-color: #dc3545;



    box-shadow: 0 0 0 2px rgba(220, 53, 69, 0.1);



}







.modal-open {



    overflow: hidden;



}







.tooltip {



    position: absolute;



    background: #333;



    color: white;



    padding: 5px 8px;



    border-radius: 4px;



    font-size: 12px;



    white-space: nowrap;



    z-index: 1000;



    top: -30px;



    left: 50%;



    transform: translateX(-50%);



    pointer-events: none;



}







.tooltip::after {



    content: '';



    position: absolute;



    top: 100%;



    left: 50%;



    margin-left: -5px;



    border-width: 5px;



    border-style: solid;



    border-color: #333 transparent transparent transparent;



}







@media (prefers-reduced-motion: reduce) {



    * {



        animation-duration: 0.01ms !important;



        animation-iteration-count: 1 !important;



        transition-duration: 0.01ms !important;



    }



}



</style>



`;







// Inject additional CSS



if (typeof document !== 'undefined') {



    document.head.insertAdjacentHTML('beforeend', additionalCSS);



}