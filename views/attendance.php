<div class="container mx-auto p-4">
    <h2 class="text-2xl font-bold mb-4">Mark Attendance</h2>
    <form action="/mark-attendance" method="POST">
        <select name="class_id" class="p-2 border rounded">
            <!-- Populate with classes from DB -->
        </select>
        <div id="student-list" class="mt-4">
            <!-- Dynamically load students via JS -->
        </div>
        <button type="submit" class="mt-4 bg-green-500 text-white p-2 rounded">Submit Attendance</button>
    </form>
</div>