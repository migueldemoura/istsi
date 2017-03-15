$(document).ready(function() {
    $('[data-toggle="tooltip"]').tooltip();

    $('nav a[href*="#"]').on('click', function(e) {
        e.preventDefault();
        $('html, body').animate({scrollTop: $(this.hash).offset().top - 50}, 500);
    });

    $('.dropdown-menu li a').click(function(e) {
        e.preventDefault();
        $(this).parents('.dropdown').find('.btn').html($(this).text() + ' <span class="caret"></span>');
        $(this).parents('.dropdown').find('.btn').val($(this).data('value'));
    });

    $('.filter-course').on('click', function() {
        $('.filter-course').removeClass('active');
        $(this).addClass('active');

        var course = $('.filter-course.active').data('filter');
        $('.filterable-course').each(function() {
            var $this = $(this);
            ((course === 'All') ?
                function() {$this.parent().show()} :
                function() {$this.parent()[($this.text().indexOf(course) !== -1) ? 'show' : 'hide']()}
            )();
        });
    });

    $('.nav a').on('click', function() {
        var a = $('.navbar-toggle');
        if (a.css('display') != 'none') {
            a.trigger('click');
        }
    });

    $('.modal').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
    });

    $(document).on('click', 'tr[data-item]', function () {
        var proposal = $(this).data('item');
        var modal = $('.modal');

        $.ajax({
            url: '/proposal/get/data/' + proposal,
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    modal.find('#company').val(response.data.company);
                    modal.find('#description').val(response.data.description);
                    modal.find('#project').val(response.data.project);
                    modal.find('#requirements').val(response.data.requirements);
                    modal.find('#salary').val(response.data.salary);
                    modal.find('#observations').val(response.data.observations);
                    modal.find('#duration').val(response.data.duration);
                    modal.find('#location').val(response.data.location);
                    modal.find('#vacancies').val(response.data.vacancies);
                } else {
                    window.alert('Erro');
                }
            },
            error: function () {
                window.alert('Erro');
            }
        });
        modal.find('.modal-title').text('Proposa ' + proposal);
        modal.modal('show');
    });
});