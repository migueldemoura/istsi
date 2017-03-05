$(document).ready(function () {
    'use strict';

    $('.dropdown a[href="#"]').on('click', function (event) {
        event.preventDefault();
    });

    function parseResponse(response, form) {
        if (response.status === 'success') {
            return true;
        } else if (response.status === 'fail') {
            if (response.data.auth) {
                window.location.replace('/');
            }
            if (response.data.info) {
                window.location.replace('/student/account');
            }
            if (form !== null) {
                var validator = $('#' + form).validate();

                validator.showErrors({
                    proposal: response.data.proposal,
                    observations: response.data.observations,
                    CV: response.data.fileCV,
                    CM: response.data.fileCM
                });
                if (response.data.files) {
                    validator.showErrors({
                        CV: response.data.files,
                        CM: response.data.files
                    });
                }
                if (response.data.all) {
                    validator.showErrors({
                        observations: response.data.all,
                        CV: response.data.all,
                        CM: response.data.all
                    });
                }
            }
            if (response.data.registration) {
                window.alert('O período de candidaturas está encerrado.');
            }
        } else if (response.status === 'error') {
            window.alert('Erro: ' + response.message);
        } else {
            window.alert('Erro');
        }
        return false;
    }

    function getTokenValue() {
        return $('#token').text();
    }

    $(document).bind('ajaxSend', function (elm, xhr) {
        xhr.setRequestHeader('X-CSRF-Token', getTokenValue());
    });

    function getSubmittedProposals() {
        var proposals = [];

        $.ajax({
            url: '/submission/get/list',
            dataType: 'json',
            async: false,
            success: function (response) {
                if (parseResponse(response, null)) {
                    proposals = response.data.proposals;
                }
            },
            error: function () {
                window.alert('Erro');
            }
        });
        return proposals;
    }

    function updateProposalList(proposals) {

        var field = $('#newproposal');
        field.empty();
        field.append('<option value="">Seleciona uma opção</option>');

        for (var i = 0; i < proposals.length; i++) {
            field.append('<option value="' + proposals[i] + '">' + proposals[i] + '</option>');
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

    var proposals = getSubmittedProposals();
    createSubmittedProposals(proposals.done);
    updateProposalList(proposals.available);
    if ((proposals.available).length === 0) {
        $('#newbutton').prop('disabled', true);
    }
    if ((proposals.done).length === 0) {
        $('#nosubmissions').removeClass('hidden');
    }

    $('#newmodal').on('hidden.bs.modal', function () {
        $('#newform').validate().resetForm();
        $(this).find('span#pathnewCV').empty();
        $(this).find('span#pathnewCM').empty();
    });
    $('#editmodal').on('hidden.bs.modal', function () {
        $('#editform').validate().resetForm();
        $(this).find('span#patheditCV').empty();
        $(this).find('span#patheditCM').empty();
    });

    $('input.input-file').on('change', function () {
        var input = $(this);
        var filename = input.val();

        if (filename !== '') {
            $('label#' + input.attr('id') + '-error').remove();
        }

        filename = filename.replace('C:\\fakepath\\', '');

        $('span#path' + input.attr('id')).text(filename);
    });


    $(document).on('click', '#viewbutton', function () {
        var proposal = $(this).data('item');
        var modal = $('#viewmodal');

        $.ajax({
            url: '/submission/get/data/' + proposal,
            dataType: 'json',
            success: function (response) {
                if (parseResponse(response, null)) {
                    modal.find('#viewobservations').val(response.data.observations);
                }
            },
            error: function () {
                window.alert('Erro');
            }
        });

        modal.find('.modal-title').text('Visualizar Candidatura - Proposta ' + proposal);

        modal.find('#viewCV').attr('href', '/submission/get/file/' + proposal + '/CV?csrf_token=' + getTokenValue());
        modal.find('#viewCM').attr('href', '/submission/get/file/' + proposal + '/CM?csrf_token=' + getTokenValue());
        modal.modal('show');
    });

    $(document).on('click', '#editbutton', function () {
        var proposal = $(this).data('item');
        var modal = $('#editmodal');

        $.ajax({
            url: '/submission/get/data/' + proposal,
            dataType: 'json',
            success: function (response) {
                if (parseResponse(response, null)) {
                    modal.find('#editobservations').val(response.data.observations);
                }
            },
            error: function () {
                window.alert('Erro');
            }
        });

        modal.find('.modal-title').text('Editar Candidatura - Proposta ' + proposal);
        modal.find('#proposal').text(proposal);

        modal.modal('show');
    });

    $(document).on('click', 'button#delete', function () {
        var proposal = $(this).data('item');
        var me = $(this);

        $.ajax({
            url: '/submission/delete/' + proposal,
            type: 'DELETE',
            dataType: 'json',
            success: function (response) {
                if (parseResponse(response, null)) {
                    var proposals = getSubmittedProposals();
                    updateProposalList(proposals.available);

                    if ((proposals.done).length === 0) {
                        $('#nosubmissions').removeClass('hidden');
                    }
                    $('#newbutton').prop('disabled', false);

                    me.closest('li').remove();
                }
            },
            error: function () {
                window.alert('Erro');
            }
        });
    });

    $('#newformSubmit').on('click', function () {
        $('#newform').submit();
    });
    $('#editformSubmit').on('click', function () {
        $('#editform').submit();
    });

    $('.modal').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
    });


    $.validator.addMethod('filesize', function (value, element, param) {
        return this.optional(element) || (element.files[0].size <= param);
    });


    $('#newform').validate({
        rules: {
            proposal: 'required',
            CV: {
                required: true,
                extension: 'pdf',
                filesize: 3145728
            },
            CM: {
                required: true,
                extension: 'pdf',
                filesize: 3145728
            }
        },
        messages: {
            proposal: 'Seleciona uma proposta',
            CV: {
                required: 'Nenhum ficheiro selecionado',
                extension: 'Ficheiro não tem extensão .pdf',
                filesize: 'Ficheiro demasiado grande'
            },
            CM: {
                required: 'Nenhum ficheiro selecionado',
                extension: 'Ficheiro não tem extensão .pdf',
                filesize: 'Ficheiro demasiado grande'
            }
        },
        submitHandler: function (form, e) {
            e.preventDefault();

            var formData = new FormData($(form)[0]);
            formData.append('type', 'new');

            var proposal = [$(form).find('select#newproposal').val()];

            $.ajax({
                url: '/submission/create/' + proposal,
                type: 'POST',
                data: formData,
                async: false,
                cache: false,
                contentType: false,
                processData: false,
                dataType: 'json',
                success: function (response) {
                    if (parseResponse(response, 'newform')) {
                        createSubmittedProposals(proposal);
                        var proposals = getSubmittedProposals();
                        updateProposalList(proposals.available);
                        if ((proposals.available).length === 0) {
                            $('#newbutton').prop('disabled', true);
                        }
                        $('#nosubmissions').addClass('hidden');
                        $('#newmodal').modal('hide');
                    }
                },
                error: function () {
                    window.alert('Erro');
                }
            });
        }
    });

    $('#editform').validate({
        rules: {
            observations: {
                maxlength: 1024
            },
            CV: {
                extension: 'pdf',
                filesize: 3145728
            },
            CM: {
                extension: 'pdf',
                filesize: 3145728
            }
        },
        messages: {
            observations: {
                maxlength: 'Observações inválidas ou demasiado extensas. Tamanho máximo permitido: 1024 caracteres.'
            },
            CV: {
                extension: 'Ficheiro não tem extensão .pdf',
                filesize: 'Ficheiro demasiado grande'
            },
            CM: {
                extension: 'Ficheiro não tem extensão .pdf',
                filesize: 'Ficheiro demasiado grande'
            }
        },
        submitHandler: function (form, e) {
            e.preventDefault();

            var formData = new FormData($(form)[0]);
            formData.append('type', 'edit');

            var proposal = [$(form).find('#proposal').text()];

            $.ajax({
                url: '/submission/update/' + proposal,
                type: 'POST',
                data: formData,
                async: false,
                cache: false,
                contentType: false,
                processData: false,
                dataType: 'json',
                success: function (response) {
                    if (parseResponse(response, 'editform')) {
                        $('#editmodal').modal('hide');
                    }
                },
                error: function () {
                    window.alert('Erro');
                }
            });
        }
    });
});
