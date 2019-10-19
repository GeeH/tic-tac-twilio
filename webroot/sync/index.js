$(function () {
    //We'll use message to tell the user what's happening
    var $message = $('#message');

    //Get handle to the game board buttons
    var $buttons = $('#board .board-row button');

    //Our interface to the Sync service
    var syncClient;

    // naughty way to get access to board data
    var boardData;

    // Grab our token which also matchmakes us into a game
    $.getJSON('/token.php', function (player) {

        // Once we have the token, we can initialize the Sync client and start subscribing
        // to data. The client will initialize asynchronously while we load the rest of
        // the user interface.
        syncClient = new Twilio.Sync.Client(player.token, {logLevel: 'info'});
        syncClient.on('connectionStateChanged', function (state) {
            if (state != 'connected') {
                $message.html('Sync is not live (websocket connection <span style="color: red">' + state + '</span>)…');
            } else {
                // Now that we're connected, lets light up our board and play!
                $buttons.attr('disabled', false);
                $message.html('Sync is live!');
            }
        });

        // Let's pop a message on the screen to show that Sync is working
        $message.html('Loading board data…');

        // Our game state is stored in a Sync document. Here, we'll attach to that document
        // (or create it, if it doesn't exist) and connect the necessary event handlers.
        syncClient.document(player.boardUuid).then(function (syncDoc) { // replace with boardUuid document
            $.boardData = syncDoc.value;
            $('#turn').html($.boardData.turn);
            $('#mark').html(player.mark);

            if ($.boardData.board) {
                updateUserInterface($.boardData);
            }

            // Any time the board changes, we want to show the new state. The 'updated'
            // event is for this.
            syncDoc.on('updated', function (event) {
                console.log("Board was updated", event.isLocal ? "locally." : "by the other person.");
                $.boardData = syncDoc.value;

                if ($.boardData.winner !== null) {
                    $buttons.attr('disabled', true);
                    $message.html($.boardData.winner + ' has WON!!!!!');
                }

                updateUserInterface(event.value);
                $('#turn').html($.boardData.turn);
            });

            // Let's make our buttons control the game state in Sync…
            $buttons.on('click', function (e) {
                // if it's not your turn, clicking should do nothing so return early
                if (player.mark !== $.boardData.turn) {
                    return;
                }

                // if the square isn't empty you shouldn't change it so return early
                if ($(e.target).html() !== '&nbsp;') {
                    return;
                }

                // cheeky dirty way of disabling game while we wait for state to update
                $.boardData.turn = '';

                toggleCellValue($(e.target), player.mark);

                // Send updated document to Sync. This will trigger "updated" events for all players.
                var data = readGameBoardFromUserInterface();
                $.post('/handle-state.php', {
                    board: JSON.stringify(data),
                    player: JSON.stringify(player)
                }, (response) => {
                    console.log(response);
                });
            });
        });

    });

    //Toggle the value: X, O, or empty (&nbsp; for UI)
    function toggleCellValue($cell, mark) {
        $cell.html(mark);
    }

    //Read the state of the UI and create a new document
    function readGameBoardFromUserInterface() {
        var board = [
            ['', '', ''],
            ['', '', ''],
            ['', '', '']
        ];

        for (var row = 0; row < 3; row++) {
            for (var col = 0; col < 3; col++) {
                var selector = '[data-row="' + row + '"]' +
                    '[data-col="' + col + '"]';
                board[row][col] = $(selector).html().replace('&nbsp;', '');
            }
        }

        return board;
    }

    //Update the buttons on the board to match our document
    function updateUserInterface(data) {
        for (var row = 0; row < 3; row++) {
            for (var col = 0; col < 3; col++) {
                var this_cell = '[data-row="' + row + '"]' + '[data-col="' + col + '"]';
                var cellValue = data.board[row][col];
                $(this_cell).html(cellValue === '' ? '&nbsp;' : cellValue);
            }
        }
    }
});
