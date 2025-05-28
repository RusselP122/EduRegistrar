document.querySelector('select[name="class_id"]').addEventListener('change', function() {
    fetch('/api/students?class_id=' + this.value)
        .then(response => response.json())
        .then(data => {
            let studentList = document.getElementById('student-list');
            studentList.innerHTML = '';
            data.students.forEach(student => {
                studentList.innerHTML += `
                    <div>
                        <label>${student.name}</label>
                        <select name="status[${student._id}]">
                            <option value="present">Present</option>
                            <option value="absent">Absent</option>
                            <option value="late">Late</option>
                        </select>
                    </div>`;
            });
        });
});