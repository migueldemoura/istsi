$(document).ready(function () {
    'use strict';

    $('.dropdown a[href="#"]').on('click', function (event) {
        event.preventDefault();
    });

    function getTokenValue() {
        return $('#token').text();
    }

    $(document).bind('ajaxSend', function (elm, xhr) {
        xhr.setRequestHeader('X-CSRF-Token', getTokenValue());
    });

    function createCourseList(courses) {
        var itemTemplate = $('#checkboxrow').html().trim();

        for (var i = 0; i < courses.acronym.length; i++) {
            var item = $(itemTemplate);
            item.find('input').attr('name', 'courses[]');
            item.find('input').attr('value', courses.acronym[i]);
            item.find('label').html(item.children('label').html() + courses.name[i]);
            $('.courses').append(item);
        }
    }

    function updateSelectedCourses(courses, disable) {
        $('.courses input').each(function() {
            $(this).prop('disabled', disable);
            $(this).prop('checked', $.inArray($(this).attr('value'), courses) !== -1);
        });
    }

    function getCourses() {
        var courses = [];

        $.ajax({
            url: '/course/get',
            dataType: 'json',
            async: false,
            success: function (response) {
                if (response.status === 'success') {
                    courses = response.data;
                } else {
                    window.alert('Erro');
                }
            },
            error: function () {
                window.alert('Erro');
            }
        });
        return courses;
    }

    function getSubmittedProposals() {
        var proposals = [];

        $.ajax({
            url: '/proposal/get/list',
            dataType: 'json',
            async: false,
            success: function (response) {
                if (response.status === 'success') {
                    proposals = response.data.proposals;
                }
            },
            error: function () {
                window.alert('Erro');
            }
        });
        return proposals;
    }

    function createSubmittedProposals(proposals) {
        var itemTemplate = $('#proposalrow').html().trim();

        for (var i = 0; i < proposals.length; i++) {
            var item = $(itemTemplate);
            item.children('.proposal').text(proposals[i]);
            item.find('button').attr('data-item', proposals[i]);
            $('#proposalslist').append(item);
        }
    }

    var proposals = getSubmittedProposals();
    createSubmittedProposals(proposals);
    if (proposals.length === 0) {
        $('#noproposals').removeClass('hidden');
    }

    createCourseList(getCourses());

    $('#newmodal').on('hidden.bs.modal', function () {
        $(this).validate().resetForm();
    });
    $('#editmodal').on('hidden.bs.modal', function () {
        $(this).validate().resetForm();
    });

    $(document).on('click', '#newbutton', function () {
        updateSelectedCourses([], false);
    });

    $(document).on('click', '#viewbutton', function () {
        var proposal = $(this).data('item');
        var modal = $('#viewmodal');

        $.ajax({
            url: '/proposal/get/data/' + proposal,
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    modal.find('#description').val(response.data.description);
                    modal.find('#project').val(response.data.project);
                    modal.find('#requirements').val(response.data.requirements);
                    modal.find('#observations').val(response.data.observations);
                    modal.find('#duration').val(response.data.duration);
                    modal.find('#location').val(response.data.location);
                    modal.find('#vacancies').val(response.data.vacancies);
                    updateSelectedCourses(response.data.courses, true);
                } else {
                    window.alert('Erro');
                }
            },
            error: function () {
                window.alert('Erro');
            }
        });

        modal.find('.modal-title').text('Visualizar Proposa ' + proposal);

        modal.modal('show');
    });

    $(document).on('click', '#editbutton', function () {
        var proposal = $(this).data('item');
        var modal = $('#editmodal');

        $.ajax({
            url: '/proposal/get/data/' + proposal,
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    modal.find('#description').val(response.data.description);
                    modal.find('#project').val(response.data.project);
                    modal.find('#requirements').val(response.data.requirements);
                    modal.find('#observations').val(response.data.observations);
                    modal.find('#duration').val(response.data.duration);
                    modal.find('#location').val(response.data.location);
                    modal.find('#vacancies').val(response.data.vacancies);
                    updateSelectedCourses(response.data.courses, false);
                } else {
                    window.alert('Erro');
                }
            },
            error: function () {
                window.alert('Erro');
            }
        });

        modal.find('.modal-title').text('Editar Proposta ' + proposal);
        modal.find('#proposal').text(proposal);

        modal.modal('show');
    });

    $(document).on('click', 'button#delete', function () {
        var proposal = $(this).data('item');
        var me = $(this);

        $.ajax({
            url: '/proposal/delete/' + proposal,
            type: 'DELETE',
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    $('#newbutton').prop('disabled', false);
                    me.closest('li').remove();
                    if (getSubmittedProposals().length === 0) {
                        $('#noproposals').removeClass('hidden');
                    }
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

    $('#newform').validate({
        rules: {
            description: 'required',
            project: 'required',
            requirements: 'required',
            observations: 'required',
            duration: 'required',
            location: 'required',
            vacancies: {
                required: true,
                digits: true
            }
        },
        messages: {
            description: 'Coloque uma breve descrição do estágio.',
            project: 'Coloque uma descrição do projeto a realizar.',
            requirements: 'Coloque os requisitos que o candidato deverá cumprir.',
            observations: 'Coloque algumas observações pertinentes.',
            duration: 'Coloque a duraçao do estagio.',
            location: 'Coloque o local onde se realizará o estágio.',
            vacancies: {
                required: 'Coloque o número de vagas disponível.',
                digits: 'O número de vagas tem que ser um inteiro.'
            }
        },
        submitHandler: function (form, e) {
            e.preventDefault();

            $.ajax({
                url: '/proposal/create',
                type: 'POST',
                data: new FormData($(form)[0]),
                async: false,
                cache: false,
                contentType: false,
                processData: false,
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'success') {
                        var proposals = getSubmittedProposals();
                        createSubmittedProposals(proposals);
                        $('#noproposals').addClass('hidden');
                        $('#newmodal').modal('hide');
                    } else {
                        window.alert('Erro');
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
            description: 'required',
            project: 'required',
            requirements: 'required',
            observations: 'required',
            duration: 'required',
            location: 'required',
            vacancies: {
                required: true,
                digits: true
            }
        },
        messages: {
            description: 'Coloque uma breve descrição do estágio.',
            project: 'Coloque uma descrição do projeto a realizar.',
            requirements: 'Coloque os requisitos que o candidato deverá cumprir.',
            observations: 'Coloque algumas observações pertinentes.',
            duration: 'Coloque a duraçao do estagio.',
            location: 'Coloque o local onde se realizará o estágio.',
            vacancies: {
                required: 'Coloque o número de vagas disponível.',
                digits: 'O número de vagas tem que ser um inteiro.'
            }
        },
        submitHandler: function (form, e) {
            e.preventDefault();

            var proposal = [$(form).find('#proposal').text()];

            $.ajax({
                url: '/proposal/update/' + proposal,
                type: 'POST',
                data: new FormData($(form)[0]),
                async: false,
                cache: false,
                contentType: false,
                processData: false,
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'success') {
                        $('#editmodal').modal('hide');
                    } else {
                        window.alert('Erro');
                    }
                },
                error: function () {
                    window.alert('Erro');
                }
            });
        }
    });
});
