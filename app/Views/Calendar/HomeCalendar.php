<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.9.0/main.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css"
        integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

    <title>Home calendar</title>
</head>

<body>







    <!-- Button to Trigger Event Creation Modal -->

    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#eventModal">
      Generate Booking
    </button>

    <!-- FullCalendar Container -->
    <div id="calendar"></div>
   
    <!-- Event Creation Modal -->
    <div class="modal fade" id="eventModal" tabindex="-1" role="dialog" aria-labelledby="eventModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="eventModalLabel">Create Event</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Form to Enter Event Details -->
                    <form method="POST" action="<?= site_url('/createevent') ?>">
                        <div class="mb-3">
                        <input type="hidden" id="userid" name="userid" value="<?= isset($userId) ? $userId : '' ?>">


                            <label for="eventTitle" class="form-label">Event Title</label>
                            <input type="text" class="form-control"  name="eventTitle" placeholder="Event Title">
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="fromDate" class="form-label">From Date</label>
                                <input type="date" class="form-control" name="fromDate">
                            </div>
                            <div class="col-md-6">
                                <label for="fromTime" class="form-label">From Time</label>
                                <input type="time" class="form-control" name="fromTime">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="toDate" class="form-label">To Date</label>
                                <input type="date" class="form-control" name="toDate">
                            </div>
                            <div class="col-md-6">
                                <label for="toTime" class="form-label">To Time</label>
                                <input type="time" class="form-control" name="toTime">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="eventDescription" class="form-label">Event Description</label>
                            <textarea class="form-control" name="eventDescription" placeholder="Event Description"
                                rows="4"></textarea>
                        </div>
                        <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" name="saveEvent">Save Event</button>
                </div>
                    </form>
                </div>
               
            </div>
        </div>
    </div>




    

    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"
        integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN"
        crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js"
        integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q"
        crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js"
        integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl"
        crossorigin="anonymous"></script>



        <script>
         document.addEventListener('DOMContentLoaded', function () {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            timeZone: 'UTC',
            themeSystem: 'bootstrap5',
            headerToolbar: {
                start: 'prev,next today',
                center: 'title',
                end: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
            },
            weekNumbers: true,
            dayMaxEvents: true, // allow "more" link when too many events,
            events: <?php echo json_encode($eventdata); ?>
                // events: [
                //     // Example event 1
                //     {
                //         title: 'Availability',
                //         start: '2023-09-21T05:00:00',
                //         end: '2023-09-21T17:00:00',
                //         allDay: false // Set to false to display as a timed event
                //     },
                //     {
                //         title: 'Availability',
                //         start: '2023-09-21T18:00:00',
                //         end: '2023-09-21T23:00:00',
                //         allDay: false // Set to false to display as a timed event
                //     },
                //     {
                //         title: 'Availability',
                //         start: '2023-10-22T05:00:00',
                //         end: '2023-10-22T17:00:00',
                //         allDay: false // Set to false to display as a timed event
                //     },
                //     // Add more events here as needed
                // ]
            });

            // Render the calendar
            calendar.render();

            // Event Click Handling
            calendar.setOption('eventClick', function (info) {
                var event = info.event;
                // Display event details in a Bootstrap popover
                $(info.el).popover({
                    title: event.title,
                    content: "Start: " + event.start.toLocaleString() + "<br>End: " + event.end.toLocaleString(),
                    placement: "top",
                    html: true,
                    trigger: "hover"
                }).popover('toggle');
            });
        });
    </script>

</body>

</html>