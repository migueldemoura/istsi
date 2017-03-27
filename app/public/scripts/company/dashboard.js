$(document).ready(function () {
    'use strict';

    var fields = [
        'description', 'project', 'requirements', 'salary', 'observations',
        'duration', 'location', 'vacancies'
    ];

    function createCourseList(courses) {
        var itemTemplate = $('#checkboxrow').html().trim();

        for (var i = 0; i < courses['acronym'].length; i++) {
            var item = $(itemTemplate);
            item.find('input').attr('name', 'courses[]');
            item.find('input').attr('value', courses['acronym'][i]);
            item.find('label span').text(courses.name[i]);
            $('.courses').append(item);
        }
    }

    function updateSelectedCourses(courses, disable) {
        $('.courses input').each(function () {
            $(this).prop('disabled', disable);
            $(this).prop('checked', $.inArray($(this).attr('value'), courses) !== -1);
        });
    }

    function createSubmittedProposals(proposals) {
        var itemTemplate = $('#proposalrow').html().trim();
        var $proposalsList = $('#proposalslist');
        $proposalsList.empty();

        for (var i = 0; i < proposals.length; i++) {
            var item = $(itemTemplate);
            item.children('.proposal').text(proposals[i]);
            item.find('button').attr('data-item', proposals[i]);
            $proposalsList.append(item);
        }
    }

    var proposals = ajaxRequest('/proposal/get/list', 'GET', false, null, function () {})['proposals'];
    createSubmittedProposals(proposals);
    if (proposals.length === 0) {
        $('#noproposals').removeClass('hidden');
    }

    createCourseList(ajaxRequest('/course/get', 'GET', false, null, function () {}));

    $(document).on('click', '#newbutton', function () {
        updateSelectedCourses([], false);
    });

    $(document).on('click', '#viewbutton', function () {
        var proposal = $(this).data('item');
        var modal = $('#viewmodal');

        ajaxRequest('/proposal/get/data/' + proposal, 'GET', false, null, function(response) {
            for (var i = 0; i < fields.length; ++i) {
                modal.find('#' + fields[i]).val(response.data[fields[i]]);
            }
            updateSelectedCourses(response.data['courses'], true);
        });

        modal.find('.modal-title').text('Visualizar Proposa ' + proposal);

        modal.modal('show');
    });

    $(document).on('click', '#editbutton', function () {
        var proposal = $(this).data('item');
        var modal = $('#editmodal');

        ajaxRequest('/proposal/get/data/' + proposal, 'GET', true, null, function(response) {
            for (var i = 0; i < fields.length; ++i) {
                modal.find('#' + fields[i]).val(response.data[fields[i]]);
            }
            updateSelectedCourses(response.data['courses'], false);
        });

        modal.find('.modal-title').text('Editar Proposta ' + proposal);
        modal.find('#proposal').text(proposal);

        modal.modal('show');
    });

    $(document).on('click', 'button#delete', function () {
        var proposal = $(this).data('item');
        var $this = $(this);

        ajaxRequest('/proposal/delete/' + proposal, 'DELETE', true, null, function () {
            $('#newbutton').prop('disabled', false);
            $this.closest('li').remove();
            if (ajaxRequest('/proposal/get/list', 'GET', false, null, function () {})
                    ['proposals'].length === 0
            ) {
                $('#noproposals').removeClass('hidden');
            }
        });
    });

    // Forms
    registerForm('#newform', '#newformSubmit',
        {'courses[]': 'required'}, {},
        function () {return '/proposal/create';}, 'POST',
        function () {}, function () {
            createSubmittedProposals(
                ajaxRequest('/proposal/get/list', 'GET', false, null, function () {})['proposals']
            );

            $('#noproposals').addClass('hidden');
            $('#newmodal').modal('hide');
        }
    );

    registerForm('#editform', '#editformSubmit',
        {'courses[]': 'required'}, {},
        function () {return '/proposal/update/' + $('#editform').find('#proposal').text();}, 'POST',
        function () {}, function () {$('#editmodal').modal('hide')}
    );
});
