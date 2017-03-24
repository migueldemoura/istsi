$(document).ready(function () {
    'use strict';

    var fields = [
        'company', 'description', 'project', 'requirements', 'salary', 'observations',
        'duration', 'location', 'vacancies'
    ];

    updateProposalListStatus();

    function updateProposalListStatus() {
        $('#noproposals')[(($('td')[0].offsetParent === null) ? 'remove' : 'add') + 'Class']('hidden');
    }

    $('[data-toggle="tooltip"]').tooltip();

    $('nav a[href*="#"]').on('click', function(e) {
        e.preventDefault();
        $('html, body').animate({scrollTop: $(this.hash).offset().top - 50}, 500);
    });

    $('.dropdown-menu li a').click(function(e) {
        e.preventDefault();
        $(this).parents('.dropdown').find('#selected-course').text($(this).text());
        $(this).parents('.dropdown').find('.btn').val($(this).data('value'));
    });

    $('.filter-course').on('click', function () {
        var $this = $(this);

        $('.filter-course.active').removeClass('active');
        $this.addClass('active');

        var course = $this.data('filter');
        $('.filterable-course').each(function () {
            var $this = $(this);
            $this.parent()[(course === 'All' || $this.text().indexOf(course) !== -1) ? 'show' : 'hide']();
        });
        updateProposalListStatus();
    });

    $('.modal').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
    });

    $(document).on('click', 'tr[data-item]', function () {
        var proposal = $(this).data('item');
        var modal = $('.modal');

        ajaxRequest('/proposal/get/data/' + proposal, 'GET', true, null, null, function(response) {
            for (var i = 0; i < fields.length; ++i) {
                modal.find('#' + fields[i]).val(response.data[fields[i]]);
            }
        });

        modal.find('.modal-title').text('Proposta ' + proposal);
        modal.modal('show');
    });
});