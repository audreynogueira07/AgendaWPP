jQuery(document).ready(function($) {
    // Inicialmente oculta o botão de agendamento
    $('#schedule-button').hide();

    // Configura o daterangepicker
    let drp = $('#date-range').daterangepicker({
        locale: {
            format: 'DD/MM/YYYY',
            applyLabel: 'Aplicar',
            cancelLabel: 'Cancelar',
            fromLabel: 'De',
            toLabel: 'Para',
            customRangeLabel: 'Personalizado',
            daysOfWeek: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'],
            monthNames: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
            firstDay: 1
        },
        opens: 'center',
        autoApply: false,
        alwaysShowCalendars: true,
        minDate: moment().add(1, 'days'),
    }, function(start, end, label) {
        if (end.isAfter(start)) {
            $('#schedule-button').show();
        } else {
            $('#schedule-button').hide();
        }
    });

    // Quando o botão "Agendar" é clicado
    $('#schedule-button').on('click', function() {
        var dates = drp.data('daterangepicker');
        var messageTemplate = ws_params.ws_whatsapp_message || "Olá, quero agendar {date_start} até {date_end}";

        var message = messageTemplate.replace('{date_start}', dates.startDate.format('DD/MM/YYYY'))
                                     .replace('{date_end}', dates.endDate.format('DD/MM/YYYY'));

        var phoneNumber = ws_params.ws_whatsapp_number;
        var whatsappUrl = "https://api.whatsapp.com/send?phone=" + phoneNumber + "&text=" + encodeURIComponent(message);

        // Oculta o botão "Agendar"
        $('#schedule-button').hide();

        // Abre o link do WhatsApp em uma nova aba
        window.open(whatsappUrl, '_blank');
    });
});
