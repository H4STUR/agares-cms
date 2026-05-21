$(document).ready(function() {
    $('.toast').toast('show');
    

    $('#universalConfirmationModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget); // Button that triggered the modal
        var modalBodyMessage = button.data('modal-body'); // Extract info from data-* attributes
        var formId = button.data('form-id'); // Form ID to submit

        var modal = $(this);
        modal.find('.modal-body').text(modalBodyMessage); // Update the modal's content.

        // When the user clicks confirm, find the form by ID and submit it.
        $('#universalConfirmAction').off('click').on('click', function() {
            document.getElementById(formId).submit();
        });
    });
});


function copyLinkToClipboard(element) {
    event.preventDefault();

    const text = element.textContent;
    // Use the Clipboard API to copy the text
    navigator.clipboard.writeText(text)
        .then(() => {
            console.log('Link copied to clipboard!');
        })
        .catch(err => {
            console.error('Failed to copy:', err);
        });
}