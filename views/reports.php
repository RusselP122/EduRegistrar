<div class="container mx-auto p-4">
    <h2 class="text-2xl font-bold mb-4">Attendance Reports</h2>
    <form action="/reports" method="GET">
        <select name="class_id" class="p-2 border rounded">
            <!-- Populate with classes -->
        </select>
        <input type="date" name="start_date" class="p-2 border rounded">
        <input type="date" name="end_date" class="p-2 border rounded">
        <button type="submit" class="bg-blue-500 text-white p-2 rounded">Generate Report</button>
    </form>
    <div id="report-output" class="mt-4">
        <!-- Display report data -->
    </div>
</div>