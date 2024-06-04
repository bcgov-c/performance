
<script>
    function resetForm() {
        $('#title').val('');
        $('#goal_type').val('');
        $('#status').val('');
        $('#tag_id').val('');
        $('#filter_start_date').val('');
        $('#filter_target_date').val('');

        $("#filter-menu").submit();
    }

    $(document).ready(function(){
        $('[data-toggle="popover"]').popover();
    });

    


    document.addEventListener('keydown', function(event) {
        const tabs = document.querySelectorAll('[role="tab"]');
        let currentIndex = Array.from(tabs).findIndex(tab => tab.getAttribute('tabindex') === '0');

        if (event.key === 'ArrowRight' || event.key === 'ArrowLeft') {
            if (event.key === 'ArrowRight') {
                currentIndex = (currentIndex + 1) % tabs.length;
            } else if (event.key === 'ArrowLeft') {
                currentIndex = (currentIndex - 1 + tabs.length) % tabs.length;
            }

            tabs.forEach(tab => tab.setAttribute('tabindex', '-1'));
            tabs[currentIndex].setAttribute('tabindex', '0');
            tabs[currentIndex].focus();
        }
    });
    
</script>


<style>
    .multiselect {
            overflow: hidden;
            text-overflow: ellipsis;
            width: 275px;
    }
    
    .alert-danger {
        color: #a94442;
        background-color: #f2dede;
        border-color: #ebccd1;
    }
    
    
    .multiselect-container{
        height: 350px; 
        overflow-y: scroll;
    }
    
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #1A5A96;
    }

        
    .btn-danger:focus {
        outline: 2px solid #1A5A96; /* Change the color and style as needed */
        box-shadow: 0 0 0 0.2rem rgba(0, 0, 0, 0.5); /* Change the shadow color to black */
    }

    .btn-primary:focus {
        outline: 2px solid #1A5A96; /* Change the color and style as needed */
        box-shadow: 0 0 0 0.2rem rgba(0, 0, 0, 0.5); /* Change the shadow color to black */
    }

    .btn-secondary:focus {
        outline: 2px solid #1A5A96; /* Change the color and style as needed */
        box-shadow: 0 0 0 0.2rem rgba(0, 0, 0, 0.5); /* Change the shadow color to black */
    }

    .tab-button:focus {
        outline: 2px solid #1A5A96; /* Change the color and style as needed */
        box-shadow: 0 0 0 0.2rem rgba(0, 0, 0, 0.5); /* Change the shadow color to black */
    }

    .btn-outline-primary:focus {
        outline: 2px solid #1A5A96; /* Change the color and style as needed */
        box-shadow: 0 0 0 0.2rem rgba(0, 0, 0, 0.5); /* Change the shadow color to black */
    }

    .btn-outline-danger:focus {
        outline: 2px solid #1A5A96; /* Change the color and style as needed */
        box-shadow: 0 0 0 0.2rem rgba(0, 0, 0, 0.5); /* Change the shadow color to black */
    }

    .btn-group:focus {
        outline: 2px solid #1A5A96; /* Change the color and style as needed */
        box-shadow: 0 0 0 0.2rem rgba(0, 0, 0, 0.5); /* Change the shadow color to black */
    }

    .btn-link:focus {
        outline: 2px solid #1A5A96; /* Change the color and style as needed */
        box-shadow: 0 0 0 0.2rem rgba(0, 0, 0, 0.5); /* Change the shadow color to black */
    }

    
    .visually-hidden {
        position: absolute;
        width: 1px;
        height: 1px;
        margin: -1px;
        padding: 0;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        border: 0;
    }

</style>    
 