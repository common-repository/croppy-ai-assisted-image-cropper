jQuery(document).ready(function($) {
    $('.croppy-logout').click(function(e) {
        e.preventDefault();
        $.ajax({
            url: croppy_ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'croppy_logout',
                nonce: croppy_ajax_object.nonce
            },
            success: function(response) {
                if(response.success) {
                    // If the response contains a redirect_url, redirect the user
                    if(response.data && response.data.redirect_url) {
                        window.location.href = response.data.redirect_url;
                    }
                } else {
                    console.error('Logout failed');
                }
            },
            error: function(e) {
                console.error('Logout failed with error: ', e);
            }
        });
    });

    $('#croppy-initialize-form').on('submit', function(e) {
        e.preventDefault();

        const email = $('#email').val();
        const password = $('#password').val();

        // AJAX call to the PHP function
        $.ajax({
            url: croppy_ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'initialize_croppy', // Action hook name
                nonce: croppy_ajax_object.nonce,
                email,
                password
            },
            success: function(response) {
                if (response.data?.errors) {
                    const errorString = response.data.errors.join(';');
                    if (errorString.includes('mail_incorrect')) {
                        $('#email').addClass('is-invalid');
                    } else {
                        $('#email').removeClass('is-invalid');
                    }
                    if (errorString.includes('application_password_incorrect')) {
                        $('#password').addClass('is-invalid');
                    } else {
                        $('#password').removeClass('is-invalid');
                    }
                    return;
                }
                const data = JSON.parse(response);
                // Redirect the user to the URL returned by the PHP function
                // console.log(data)
                window.location.href = data.url;
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
            }
        });
    });
});