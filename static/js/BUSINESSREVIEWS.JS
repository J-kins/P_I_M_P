// BUSINESSREVIEWS.JS
// Handles review response form toggling and submission

document.addEventListener('DOMContentLoaded', function() {
    var respondBtns = document.querySelectorAll('.respond-btn');
    var responseSection = document.getElementById('responseSection');
    var responseForm = document.getElementById('responseForm');
    var responseReviewId = document.getElementById('responseReviewId');
    var cancelResponse = document.getElementById('cancelResponse');

    respondBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            var reviewId = btn.getAttribute('data-review-id');
            responseReviewId.value = reviewId;
            responseSection.style.display = 'block';
            window.scrollTo({ top: responseSection.offsetTop - 40, behavior: 'smooth' });
        });
    });

    cancelResponse.addEventListener('click', function() {
        responseSection.style.display = 'none';
        responseForm.reset();
    });

    responseForm.addEventListener('submit', function(e) {
        e.preventDefault();
        // Integrate with backend: send response via AJAX/fetch
        var reviewId = responseReviewId.value;
        var responseText = document.getElementById('responseText').value;
        // Example: send to /api/review/respond
        fetch('/api/review/respond', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ review_id: reviewId, response: responseText })
        })
        .then(res => res.json())
        .then(data => {
            // Show success, update UI, etc.
            alert('Response submitted!');
            responseSection.style.display = 'none';
            responseForm.reset();
            // Optionally reload reviews or update DOM
        })
        .catch(() => {
            alert('Failed to submit response.');
        });
    });
});
