<form method="POST"
      action="{{ route('student-progressions.process') }}">

    @csrf

    <input type="hidden"
           name="academic_year_id"
           value="{{ \App\Models\AcademicYear::latest()->first()->id }}">

    <table class="table table-bordered align-middle">

        <thead>

        <tr>
            <th>Student</th>
            <th>Current Class</th>
            <th>Action</th>
            <th>Next Class</th>
            <th>Remarks</th>
        </tr>

        </thead>

        <tbody>

        @foreach($students as $student)

        <tr>

            <td>
                {{ $student->full_name }}
            </td>

            <td>
                {{ $class->name }}
            </td>

            <td>

                <select
                    name="students[{{ $student->id }}][action]"
                    class="form-select">

                    <option value="promoted">
                        Promote
                    </option>

                    <option value="repeated">
                        Repeat
                    </option>

                    <option value="graduated">
                        Graduate
                    </option>

                </select>

            </td>

            <td>

                <select
                    name="students[{{ $student->id }}][to_class_id]"
                    class="form-select">

                    <option value="">
                        -- Select Class --
                    </option>

                    @foreach($classes as $nextClass)

                        <option value="{{ $nextClass->id }}">
                            {{ $nextClass->name }}
                        </option>

                    @endforeach

                </select>

            </td>

            <td>

                <input type="text"
                       class="form-control"
                       name="students[{{ $student->id }}][remarks]">

            </td>

        </tr>

        @endforeach

        </tbody>

    </table>

    <button class="btn btn-success">

        <i class="fas fa-check-circle me-1"></i>

        Process Progressions

    </button>

</form>