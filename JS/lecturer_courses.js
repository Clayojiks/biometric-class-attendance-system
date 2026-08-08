document.addEventListener("DOMContentLoaded", function () {

    console.log("Lecturer Courses JS Loaded");

    const buttons = document.querySelectorAll(".view-students");

    buttons.forEach(function(button){

        button.addEventListener("click", function(){

            const courseId = this.getAttribute("data-course");

            window.location.href =
                "lecturer_course_students.php?course_id=" + courseId;

        });

    });

});