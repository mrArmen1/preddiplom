$(document).ready(function() {
    $(window).scroll(function() {
        if ($(this).scrollTop() > 10) {
            $('#backToTopBtn').fadeIn();
        } else {
            $('#backToTopBtn').fadeOut();
        }
    });

    $('#backToTopBtn').click(function() {
        $('html, body').animate({ scrollTop: 0 }, 800);
        return false;
    });
});

function openYouTubePage() {
    var youtubeURL = 'https://youtu.be/7Dzby73h8oE?si=iTtV75fp23HDLKQ0';
    window.open(youtubeURL, '_blank');
}


