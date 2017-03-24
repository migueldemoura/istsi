$(document).ready(function () {
    'use strict';

    var user = 'student';

    var fileRule = {
        extension: 'pdf',
        filesize: 3145728
    };
    var fileMessage = {
        extension: 'Ficheiro não tem extensão .pdf',
        filesize: 'Ficheiro demasiado grande'
    };

    var proposals = ajaxRequest('/submission/get/list', 'GET', false, user, null, function () {})['proposals'];
    createSubmittedProposals(proposals['done']);
    updateProposalList(proposals['available']);
    if ((proposals['available']).length === 0) {
        $('#newbutton').prop('disabled', true);
    }
    if ((proposals['done']).length === 0) {
        $('#nosubmissions').removeClass('hidden');
    }


    function updateProposalList(proposals) {
        var itemTemplate = $('#optionrow').html().trim();
        var field = $('#newproposal');

        field.empty();
        field.append($(itemTemplate));

        for (var i = 0; i < proposals.length; i++) {
            var item = $(itemTemplate);
            item.text(proposals[i]).val(proposals[i]);
            field.append(item);
        }
    }

    function createSubmittedProposals(proposals) {
        var itemTemplate = $('#submissionrow').html().trim();

        for (var i = 0; i < proposals.length; i++) {
            var item = $(itemTemplate);
            item.children('.proposal').text(proposals[i]);
            item.find('button').attr('data-item', proposals[i]);
            $('#submissionslist').append(item);
        }
    }

    $('input.input-file').on('change', function () {
        var input = $(this);
        var filename = input.val().replace('C:\\fakepath\\', '');
        if (filename !== '') {
            $('label#' + input.attr('id') + '-error').remove();
        }
        $('span#path' + input.attr('id')).text(filename);
    });

    $(document).on('click', '#viewbutton', function () {
        var proposal = $(this).data('item');
        var modal = $('#viewmodal');

        ajaxRequest('/submission/get/data/' + proposal, 'GET', true, user, null, function(response) {
            modal.find('#viewobservations').val(response.data['observations']);
        });
        modal.find('.modal-title').text('Visualizar Candidatura - Proposta ' + proposal);
        modal.find('#viewCV').attr('href', '/submission/get/file/' + proposal + '/CV?csrf_token=' + getToken());
        modal.find('#viewCM').attr('href', '/submission/get/file/' + proposal + '/CM?csrf_token=' + getToken());
        modal.modal('show');
    });

    $(document).on('click', '#editbutton', function () {
        var proposal = $(this).data('item');
        var modal = $('#editmodal');

        ajaxRequest('/submission/get/data/' + proposal, 'GET', true, user, null, function(response) {
            modal.find('#editobservations').val(response.data['observations']);
        });
        modal.find('.modal-title').text('Editar Candidatura - Proposta ' + proposal);
        modal.find('#proposal').text(proposal);
        modal.modal('show');
    });

    $(document).on('click', 'button#delete', function () {
        var proposal = $(this).data('item');
        var $this = $(this);

        ajaxRequest('/submission/delete/' + proposal, 'DELETE', true, user, null, function () {
            var proposals = ajaxRequest('/submission/get/list', 'GET', false, user, null, function () {})
                ['proposals'];
            updateProposalList(proposals['available']);
            if (proposals['done'].length === 0) {
                $('#nosubmissions').removeClass('hidden');
            }
            $('#newbutton').prop('disabled', false);
            $this.closest('li').remove();
        });
    });

    // Forms
    registerForm('#newform', '#newformSubmit',
        {CV: fileRule, CM: fileRule}, {CV: fileMessage, CM: fileMessage},
        function () {return '/submission/create/' + $('#newform').find('#newproposal').val();}, 'POST',
        user, function () {}, function () {
            createSubmittedProposals([$('#newform').find('#newproposal').val()]);
            var proposals = ajaxRequest('/submission/get/list', 'GET', false, user, null, function () {})['proposals'];
            updateProposalList(proposals['available']);
            if (proposals['available'].length === 0) {
                $('#newbutton').prop('disabled', true);
            }
            $('#nosubmissions').addClass('hidden');
            $('#newmodal').modal('hide');
        }
    );

    registerForm('#editform', '#editformSubmit',
        {CV: fileRule, CM: fileRule}, {CV: fileMessage, CM: fileMessage},
        function () {return '/submission/update/' + $('#editform').find('#proposal').text();}, 'POST',
        user, function () {}, function () {$('#editmodal').modal('hide')}
    );
});
