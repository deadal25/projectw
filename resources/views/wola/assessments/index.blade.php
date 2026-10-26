<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <!-- Konteks Penilai Aktif -->
            <div class="flex items-center space-x-3">
                <a href="{{ route('wola.groups.assessments.index', $group) }}"
                   class="p-2 rounded-xl text-slate-500 hover:text-slate-800 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                   title="Ganti Penilai">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div>
                    <div class="flex items-center space-x-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Penilai:</span>
                        @if($currentAssessor->is_leader)
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-200">
                                <svg class="w-3 h-3 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 2a1 1 0 01.894.553l1.618 3.279 3.619.526a1 1 0 01.554 1.705l-2.618 2.552.618 3.604a1 1 0 01-1.451 1.054L10 13.567l-3.236 1.702a1 1 0 01-1.451-1.054l.618-3.604-2.618-2.552a1 1 0 01.554-1.705l3.619-.526L9.106 2.553A1 1 0 0110 2z" clip-rule="evenodd"/></svg>
                                Ketua
                            </span>
                        @endif
                    </div>
                    <h2 class="font-extrabold text-xl text-slate-900 dark:text-white leading-tight mt-0.5">
                        {{ $currentAssessor->employee->fullname }}
                        <span class="font-mono text-sm text-slate-500 font-normal">({{ $currentAssessor->employee->nrp }})</span>
                    </h2>
                </div>
            </div>

            <!-- Tombol Aksi Header -->
            <div class="flex items-center space-x-3">
                <!-- Tombol Ganti Penilai -->
                <a href="{{ route('wola.groups.assessments.index', $group) }}"
                   class="px-3.5 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-xs font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition flex items-center space-x-1.5">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                    <span>Ganti Penilai</span>
                </a>

                <!-- Toggle Dua Mode -->
                <div class="inline-flex p-1 bg-slate-100 dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700">
                    <a href="{{ route('wola.groups.assessments.index', [$group, 'assessor_id' => $currentAssessor->id, 'mode' => 'by_job', 'job_id' => $currentJob ? $currentJob->id : null]) }}"
                       class="px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center space-x-1.5 {{ $mode === 'by_job' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900' }}">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <span>Per Jabatan</span>
                    </a>
                    <a href="{{ route('wola.groups.assessments.index', [$group, 'assessor_id' => $currentAssessor->id, 'mode' => 'by_employee', 'employee_id' => $currentEmployee ? $currentEmployee->id : null]) }}"
                       class="px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center space-x-1.5 {{ $mode === 'by_employee' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900' }}">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span>Per Karyawan</span>
                    </a>
                </div>
            </div>
        </div>
    </x-slot>

    <!-- Workspace Penilaian dengan Reaktif Alpine.js -->
    <script>
        function assessmentWorkspace() {
            return {
                mode: @json($mode),
                assessorId: {{ $currentAssessor->id }},
                currentJobId: {{ $currentJob ? $currentJob->id : 'null' }},
                currentEmployeeId: {{ $currentEmployee ? $currentEmployee->id : 'null' }},
                
                // State centang milik penilai aktif saat ini: { 'jobId_empId': true/object }
                myAssessments: @json($myAssessments),

                // State centang milik penilai lain dalam kelompok: { 'jobId_empId': { assessor_name: '...', names_string: '...', count: N } }
                otherAssessments: @json($otherAssessments),

                // State seluruh penilaian per unit (semua penilai yang menilai unit tersebut): { 'jobId_empId': { names: [...], names_string: '...', count: N } }
                allAssessmentsByUnit: @json($allAssessmentsByUnit),

                // Indikator auto-save: 'saved', 'saving', 'error'
                saveStatus: 'saved',
                saveMessage: 'Tersimpan',

                // Filter lokal
                statusFilter: 'all', // 'all', 'checked', 'unchecked', 'my', 'other'
                searchQuery: '',

                // Notifikasi Pemberitahuan 'Sudah Dinilai'
                lockedNotice: {
                    show: false,
                    empName: '',
                    jobName: '',
                    assessorName: ''
                },
                toastNotice: {
                    show: false,
                    message: '',
                    type: 'warning'
                },
                showToast(message, type = 'warning') {
                    this.toastNotice.message = message;
                    this.toastNotice.type = type;
                    this.toastNotice.show = true;
                    setTimeout(() => { this.toastNotice.show = false; }, 4500);
                },

                // Daftar seluruh karyawan untuk navigasi otomatis
                employeesList: [
                    @foreach($employees as $emp)
                    {
                        id: {{ $emp->id }},
                        fullname: @json($emp->fullname),
                        nrp: @json($emp->nrp),
                        dept: @json($emp->dept),
                        section: @json($emp->section)
                    },
                    @endforeach
                ],
                selectedEmployeeId: {{ $employees->first() ? $employees->first()->id : 'null' }},
                selectedEmployeeName: @json($employees->first() ? $employees->first()->fullname : ''),
                selectedEmployeeNrp: @json($employees->first() ? $employees->first()->nrp : ''),
                newCommentEmployeeId: {{ $employees->first() ? $employees->first()->id : 'null' }},

                init() {
                    // Cari karyawan pertama yang belum dinilai untuk dipilih otomatis
                    if (this.employeesList && this.employeesList.length > 0) {
                        const unassessed = this.employeesList.find(e => !this.isMyAssessment(this.currentJobId, e.id));
                        if (unassessed) {
                            this.selectEmployee(unassessed, false);
                        }
                    }
                },

                selectEmployee(emp, shouldScroll = false, focusInput = false) {
                    if (!emp) return;
                    this.selectedEmployeeId = emp.id;
                    this.selectedEmployeeName = emp.fullname;
                    this.selectedEmployeeNrp = emp.nrp;
                    this.newCommentEmployeeId = emp.id;
                    if (shouldScroll) {
                        const el = document.getElementById('commentSection');
                        if (el) el.scrollIntoView({ behavior: 'smooth' });
                    }
                    if (focusInput) {
                        this.$nextTick(() => {
                            const input = document.getElementById('commentInput');
                            if (input) input.focus();
                        });
                    }
                },

                handleEmployeeCardClick(emp, fromCheckbox = false) {
                    if (!emp) return;
                    const isAlreadyAssessed = this.isMyAssessment(this.currentJobId, emp.id);

                    if (fromCheckbox && isAlreadyAssessed) {
                        if (confirm('Apakah Anda yakin ingin membatalkan penilaian untuk ' + emp.fullname + '?')) {
                            this.toggleAssessment(this.currentJobId, emp.id, emp.fullname, '{{ addslashes($currentJob->name ?? '') }}', false);
                        }
                        return;
                    }

                    // Selalu pilih karyawan ini dan arahkan ke form komentar di bawah
                    this.selectEmployee(emp, true, true);

                    if (!isAlreadyAssessed) {
                        this.showToast('Karyawan ' + emp.fullname + ' dipilih. Tulis catatan evaluasi di bawah lalu klik "Kirim Catatan & Simpan Penilaian" untuk menyimpan.', 'info');
                    }
                },

                selectNextEmployee() {
                    if (!this.employeesList || this.employeesList.length === 0) return;
                    const currentIdx = this.employeesList.findIndex(e => e.id === this.selectedEmployeeId);
                    let nextIdx = (currentIdx + 1) % this.employeesList.length;
                    for (let i = 1; i < this.employeesList.length; i++) {
                        let idx = (currentIdx + i) % this.employeesList.length;
                        let emp = this.employeesList[idx];
                        if (!this.isMyAssessment(this.currentJobId, emp.id)) {
                            nextIdx = idx;
                            break;
                        }
                    }
                    this.selectEmployee(this.employeesList[nextIdx], false);
                },

                selectPrevEmployee() {
                    if (!this.employeesList || this.employeesList.length === 0) return;
                    const currentIdx = this.employeesList.findIndex(e => e.id === this.selectedEmployeeId);
                    let prevIdx = (currentIdx - 1 + this.employeesList.length) % this.employeesList.length;
                    this.selectEmployee(this.employeesList[prevIdx], false);
                },

                setCommentMode(mode) {
                    if (mode === 'general') {
                        this.newCommentEmployeeId = null;
                    } else {
                        this.newCommentEmployeeId = this.selectedEmployeeId;
                    }
                },

                // Fitur Catatan & Komentar Evaluasi Jabatan
                isCommentsOpen: true,
                newCommentText: '',
                isSubmittingComment: false,
                comments: [
                    @foreach($jobComments as $jc)
                    {
                        id: {{ $jc->id }},
                        comment: @json($jc->comment),
                        assessor_id: {{ $jc->assessor_id }},
                        assessor_name: @json($jc->assessor && $jc->assessor->employee ? $jc->assessor->employee->fullname : 'Penilai'),
                        is_leader: {{ $jc->assessor && $jc->assessor->is_leader ? 'true' : 'false' }},
                        user_name: @json($jc->user ? $jc->user->name : ''),
                        employee_id: {{ $jc->employee_id ? $jc->employee_id : 'null' }},
                        target_employee_name: @json($jc->employee ? $jc->employee->fullname : null),
                        target_employee_nrp: @json($jc->employee ? $jc->employee->nrp : null),
                        created_at_human: @json($jc->created_at->diffForHumans()),
                        created_at_formatted: @json($jc->created_at->format('d M Y, H:i')),
                        can_delete: {{ (Auth::user()->hasRole(['superadmin', 'admin']) || Auth::id() === $jc->user_id || ($isKetuaOnly && (int)Auth::user()->group_id === (int)$group->id)) ? 'true' : 'false' }}
                    },
                    @endforeach
                ],

                async submitComment() {
                    if (!this.currentJobId) {
                        this.showToast('Harap pilih posisi jabatan terlebih dahulu.', 'warning');
                        return;
                    }
                    if (!this.newCommentText || !this.newCommentText.trim()) {
                        const targetName = this.selectedEmployeeName ? (' untuk ' + this.selectedEmployeeName) : '';
                        this.showToast('Harap tuliskan isi catatan / komentar evaluasi' + targetName + ' terlebih dahulu sebelum menyimpan.', 'warning');
                        const input = document.getElementById('commentInput');
                        if (input) input.focus();
                        return;
                    }

                    this.isSubmittingComment = true;
                    try {
                        const response = await fetch('{{ route('wola.groups.assessments.comments.store', $group) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                job_id: this.currentJobId,
                                assessor_id: this.assessorId,
                                employee_id: this.newCommentEmployeeId ? this.newCommentEmployeeId : null,
                                comment: this.newCommentText
                            })
                        });

                        const data = await response.json();
                        if (!response.ok || !data.success) {
                            throw new Error(data.message || 'Gagal menyimpan komentar');
                        }

                        const newC = data.comment;
                        newC.can_delete = true;
                        this.comments.push(newC);
                        const empName = newC.target_employee_name;
                        this.newCommentText = '';

                        if (data.auto_assessed && data.employee_id && data.job_id) {
                            const key = data.job_id + '_' + data.employee_id;
                            this.myAssessments[key] = true;
                            this.allAssessmentsByUnit[key] = {
                                names: data.all_names || ['Anda'],
                                names_string: data.all_names_string || 'Anda',
                                count: data.all_count || 1,
                                has_my: true,
                                has_other: (data.other_assessors_count > 0)
                            };
                            if (data.other_assessor) {
                                this.otherAssessments[key] = {
                                    names_string: data.other_assessor,
                                    assessor_name: data.other_assessor,
                                    count: data.other_assessors_count || 1,
                                    assessors: []
                                };
                            }
                            this.showToast('Catatan disimpan & ' + (empName || 'karyawan') + ' otomatis ditandai Dinilai!', 'success');
                            // Otomatis pindah ke karyawan berikutnya yang belum dinilai
                            this.selectNextEmployee();
                        } else {
                            this.showToast('Catatan umum berhasil disimpan!', 'success');
                        }
                    } catch (err) {
                        console.error(err);
                        this.showToast(err.message || 'Gagal menyimpan komentar.', 'warning');
                    } finally {
                        this.isSubmittingComment = false;
                    }
                },

                async deleteComment(commentId) {
                    if (!confirm('Apakah Anda yakin ingin menghapus catatan komentar ini?')) return;

                    try {
                        const url = '{{ route('wola.groups.assessments.comments.destroy', [$group, ':id']) }}'.replace(':id', commentId);
                        const response = await fetch(url, {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        });

                        const data = await response.json();
                        if (!response.ok || !data.success) {
                            throw new Error(data.message || 'Gagal menghapus komentar');
                        }

                        this.comments = this.comments.filter(c => c.id !== commentId);
                        this.showToast('Komentar berhasil dihapus.', 'info');
                    } catch (err) {
                        console.error(err);
                        this.showToast(err.message || 'Gagal menghapus komentar.', 'warning');
                    }
                },

                // Helper Cek Penilaian
                isMyAssessment(jobId, empId) {
                    return !!this.myAssessments[jobId + '_' + empId];
                },
                isOtherAssessment(jobId, empId) {
                    return !!this.otherAssessments[jobId + '_' + empId];
                },
                isAssessed(jobId, empId) {
                    const key = jobId + '_' + empId;
                    return !!(this.allAssessmentsByUnit[key] && this.allAssessmentsByUnit[key].count > 0)
                        || this.isMyAssessment(jobId, empId) 
                        || this.isOtherAssessment(jobId, empId);
                },
                getOtherAssessor(jobId, empId) {
                    return this.otherAssessments[jobId + '_' + empId] || { 
                        assessor_name: 'Penilai Lain', 
                        names_string: 'Penilai Lain', 
                        is_leader: false, 
                        count: 0,
                        assessors: []
                    };
                },
                getUnitAssessors(jobId, empId) {
                    const key = jobId + '_' + empId;
                    if (this.allAssessmentsByUnit[key] && this.allAssessmentsByUnit[key].count > 0) {
                        return this.allAssessmentsByUnit[key];
                    }
                    if (this.isMyAssessment(jobId, empId)) {
                        return {
                            names: ['Anda'],
                            names_string: 'Anda',
                            count: 1,
                            has_my: true,
                            has_other: false
                        };
                    }
                    if (this.isOtherAssessment(jobId, empId)) {
                        const o = this.getOtherAssessor(jobId, empId);
                        return {
                            names: [o.names_string || o.assessor_name],
                            names_string: o.names_string || o.assessor_name,
                            count: o.count || 1,
                            has_my: false,
                            has_other: true
                        };
                    }
                    return {
                        names: [],
                        names_string: '',
                        count: 0,
                        has_my: false,
                        has_other: false
                    };
                },

                // Handler klik item di mode by_employee
                handleCardClick(jobId, empId, empName, jobName) {
                    if (this.isMyAssessment(jobId, empId)) {
                        if (confirm('Apakah Anda yakin ingin membatalkan penilaian untuk ' + jobName + '?')) {
                            this.toggleAssessment(jobId, empId, empName, jobName, false);
                        }
                    } else {
                        this.showToast('Penilaian harus dilakukan pada mode Per Posisi Jabatan dengan menyertakan catatan evaluasi.', 'warning');
                    }
                },

                // Toggle centang satuan (Hanya untuk pembatalan / uncheck; centang baru harus lewat kirim catatan)
                async toggleAssessment(jobId, empId, empName = '', jobName = '', forceState = null) {
                    const key = jobId + '_' + empId;
                    const currentState = this.isMyAssessment(jobId, empId);
                    const newState = forceState !== null ? forceState : !currentState;

                    // Cegah simpan langsung tanpa catatan evaluasi
                    if (newState) {
                        this.selectEmployee({ id: empId, fullname: empName }, true, true);
                        this.showToast('Penilaian tidak dapat disimpan langsung tanpa catatan komentar. Harap tuliskan catatan evaluasi di bawah untuk ' + empName + '.', 'warning');
                        return;
                    }

                    // Optimistic update untuk pembatalan
                    delete this.myAssessments[key];

                    this.saveStatus = 'saving';
                    this.saveMessage = 'Membatalkan penilaian...';

                    try {
                        const response = await fetch('{{ route('wola.groups.assessments.toggle', $group) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                assessor_id: this.assessorId,
                                job_id: jobId,
                                employee_id: empId,
                                checked: 0
                            })
                        });

                        const data = await response.json();

                        if (!response.ok || !data.success) {
                            // Rollback jika ditolak
                            this.myAssessments[key] = true;
                            this.saveStatus = 'error';
                            this.saveMessage = data.message || 'Gagal membatalkan!';
                            this.showToast(data.message || 'Tidak dapat membatalkan penilaian.', 'warning');
                            setTimeout(() => {
                                if (this.saveStatus === 'error') {
                                    this.saveStatus = 'saved';
                                    this.saveMessage = 'Tersimpan';
                                }
                            }, 4500);
                            return;
                        }

                        // Sinkronisasi data real-time seluruh penilai
                        this.allAssessmentsByUnit[key] = {
                            names: data.all_names || [],
                            names_string: data.all_names_string || '',
                            count: data.all_count || 0,
                            has_my: false,
                            has_other: (data.other_assessors_count > 0)
                        };

                        if (data.other_assessor) {
                            this.otherAssessments[key] = {
                                names_string: data.other_assessor,
                                assessor_name: data.other_assessor,
                                count: data.other_assessors_count || 1,
                                assessors: []
                            };
                        } else {
                            delete this.otherAssessments[key];
                        }
                    } catch (err) {
                        console.error(err);
                        this.myAssessments[key] = true;
                        this.saveStatus = 'error';
                        this.saveMessage = 'Gagal membatalkan!';
                        this.showToast('Terjadi kesalahan saat membatalkan penilaian.', 'warning');
                    }
                },

                // Batch Ceklis Semua / Hapus Semua Ceklis
                async batchAction(actionType, targetIds, type = 'employee') {
                    if (targetIds.length === 0) return;

                    const validIds = targetIds;
                    this.saveStatus = 'saving';
                    this.saveMessage = actionType === 'check_all' ? 'Menceklis ' + validIds.length + ' item...' : 'Menghapus ceklis...';

                    // Optimistic update
                    validIds.forEach(id => {
                        const key = type === 'employee' ? (this.currentJobId + '_' + id) : (id + '_' + this.currentEmployeeId);
                        if (actionType === 'check_all') {
                            this.myAssessments[key] = true;
                        } else {
                            delete this.myAssessments[key];
                        }
                    });

                    try {
                        const payload = {
                            assessor_id: this.assessorId,
                            action: actionType,
                            target_ids: validIds,
                        };
                        if (this.mode === 'by_job') {
                            payload.job_id = this.currentJobId;
                        } else {
                            payload.employee_id = this.currentEmployeeId;
                        }

                        const response = await fetch('{{ route('wola.groups.assessments.batch', $group) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(payload)
                        });

                        if (!response.ok) throw new Error('Gagal eksekusi massal');
                        this.saveStatus = 'saved';
                        this.saveMessage = 'Tersimpan';
                        this.showToast(actionType === 'check_all' ? 'Berhasil menceklis ' + validIds.length + ' item yang belum dinilai.' : 'Berhasil menghapus ceklis.', 'success');
                    } catch (err) {
                        console.error(err);
                        this.saveStatus = 'error';
                        this.saveMessage = 'Gagal menyimpan massal!';
                        this.showToast('Gagal memproses centang massal.', 'warning');
                        setTimeout(() => {
                            if (this.saveStatus === 'error') {
                                this.saveStatus = 'saved';
                                this.saveMessage = 'Tersimpan';
                            }
                        }, 4500);
                    }
                }
            };
        }
    </script>

    <div class="py-6" x-data="assessmentWorkspace()">
        
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(!$isKetuaOnly && $allGroups->count() > 1)
                <!-- Selektor Cepat Area Kelompok untuk Administrator -->
                <div class="bg-white dark:bg-slate-800 p-4 sm:p-5 rounded-2xl shadow-sm border border-slate-200/90 dark:border-slate-700/80">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3.5">
                        <div class="flex items-center space-x-2.5">
                            <span class="p-2 rounded-xl bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 border border-blue-100 dark:border-blue-800/60 shadow-2xs">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                </svg>
                            </span>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-black uppercase tracking-wider text-slate-800 dark:text-slate-200">
                                        Mode Administrator: Pilih Area Kelompok
                                    </span>
                                    <span class="hidden sm:inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-700 dark:bg-blue-900/60 dark:text-blue-300">
                                        Lembar Penilaian
                                    </span>
                                </div>
                                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">
                                    Beralih langsung ke kelompok kerja lain untuk mengisi atau memverifikasi ceklis
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2 px-3 py-1.5 rounded-xl bg-slate-50 dark:bg-slate-900/70 border border-slate-200/80 dark:border-slate-700 text-xs shrink-0 self-start sm:self-center">
                            <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
                            <span class="text-slate-500 dark:text-slate-400">Area Terpilih:</span>
                            <strong class="text-blue-600 dark:text-blue-400 font-extrabold">{{ $group->name }}</strong>
                        </div>
                    </div>

                    <!-- Tombol Pill Kelompok (1 sampai N) -->
                    <div class="p-1.5 bg-slate-50 dark:bg-slate-900/60 rounded-xl border border-slate-200/80 dark:border-slate-800">
                        <div class="flex items-center gap-2 overflow-x-auto p-1.5 scrollbar-thin">
                            @foreach($allGroups as $g)
                                @php
                                    $isActive = ($g->id == $group->id);
                                @endphp
                                <a href="{{ route('wola.groups.assessments.index', $g) }}"
                                   title="Kelompok {{ $g->number }}: {{ $g->name }}"
                                   class="min-w-[46px] h-9 px-3 rounded-lg text-xs font-black whitespace-nowrap transition-all duration-150 flex items-center justify-center space-x-1 shrink-0
                                   {{ $isActive 
                                        ? 'bg-blue-600 text-white shadow-md shadow-blue-500/25 ring-2 ring-blue-500/40 dark:ring-blue-400/50' 
                                        : 'bg-white dark:bg-slate-800 hover:bg-blue-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700 hover:border-blue-300 dark:hover:border-blue-500 hover:text-blue-600 shadow-2xs' }}">
                                    <span class="text-[10px] font-bold opacity-75 {{ $isActive ? 'text-blue-100' : 'text-slate-400 dark:text-slate-500' }}">K-</span>
                                    <span>{{ $g->number }}</span>
                                    @if($isActive)
                                        <svg class="w-3.5 h-3.5 text-white ms-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Banner Pemberitahuan Item Terkunci (Ketika Mengklik Item yang Sudah Dinilai Orang Lain) -->
            <div x-show="lockedNotice.show" x-cloak style="display: none;" x-transition class="p-4 rounded-2xl bg-amber-50 border border-amber-300 text-amber-950 flex items-start justify-between shadow-xs">
                <div class="flex items-start space-x-3">
                    <span class="p-2 rounded-xl bg-amber-200 text-amber-800 shrink-0 mt-0.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </span>
                    <div>
                        <div class="flex items-center space-x-2">
                            <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase bg-amber-200 text-amber-900">
                                Sudah Dinilai (Terkunci)
                            </span>
                            <span class="text-xs font-bold text-amber-700">Mencegah Penilaian Dobel</span>
                        </div>
                        <h4 class="font-black text-sm text-amber-950 mt-1">
                            Karyawan <span x-text="lockedNotice.empName" class="underline font-extrabold"></span> untuk Jabatan <span x-text="lockedNotice.jobName" class="underline font-extrabold"></span>
                        </h4>
                        <p class="text-xs text-amber-800 mt-1 leading-relaxed">
                            Posisi ini telah dinilai oleh <strong x-text="lockedNotice.assessorName" class="font-extrabold text-amber-950"></strong>. Sesuai ketentuan, setiap nama karyawan dengan jabatan yang sama tidak dapat dinilai dobel agar data tidak ganda dan proses evaluasi lebih cepat.
                        </p>
                    </div>
                </div>
                <button type="button" @click="lockedNotice.show = false" class="p-1 rounded-lg text-amber-600 hover:bg-amber-200 transition shrink-0 ml-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Sticky Save Indicator & Context Header Bar -->
            <div class="sticky top-16 z-20 bg-white/95 dark:bg-slate-900/95 backdrop-blur-md p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="flex items-center space-x-3">
                    <!-- Status Indicator Badge -->
                    <div class="flex items-center space-x-2">
                        <template x-if="saveStatus === 'saved'">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-300">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 me-1.5 animate-pulse"></span>
                                <span x-text="saveMessage"></span>
                            </span>
                        </template>
                        <template x-if="saveStatus === 'saving'">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-300">
                                <svg class="animate-spin -ml-0.5 mr-1.5 h-3 w-3 text-blue-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                <span x-text="saveMessage"></span>
                            </span>
                        </template>
                        <template x-if="saveStatus === 'error'">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 dark:bg-rose-900/60 dark:text-rose-300">
                                <span class="w-2 h-2 rounded-full bg-rose-500 me-1.5"></span>
                                <span x-text="saveMessage"></span>
                            </span>
                        </template>
                    </div>

                    <!-- Keterangan Progres Penilai Aktif -->
                    <span class="text-xs text-slate-500 hidden sm:inline">
                        Kontribusi Penilai: <strong>{{ $assessorProgress['checked'] }}</strong> checklist terisi
                    </span>
                </div>

                <div class="flex items-center space-x-2 text-xs">
                    <span class="text-slate-400">Status Petunjuk:</span>
                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-md border border-blue-100">
                        <span class="w-2 h-2 rounded-full bg-blue-600"></span> Dinilai Anda
                    </span>
                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-amber-800 bg-amber-50 px-2 py-0.5 rounded-md border border-amber-200">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span> Sudah Dinilai Lain
                    </span>
                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-slate-600 bg-slate-100 px-2 py-0.5 rounded-md border border-slate-200">
                        <span class="w-2 h-2 rounded-full bg-slate-400"></span> Belum
                    </span>
                </div>
            </div>

            <!-- ============================================================= -->
            <!-- MODE 1: PER JABATAN -->
            <!-- ============================================================= -->
            @if($mode === 'by_job')
                <!-- Selector Horizontal Posisi Jabatan -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-5">
                    <div class="flex items-center justify-between mb-3">
                        <label class="text-xs font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            Pilih Posisi Jabatan:
                        </label>
                        <span class="text-xs text-slate-400">Total {{ $jobs->count() }} Jabatan</span>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3">
                        @foreach($jobs as $job)
                            @php
                                $stat = $jobStats[$job->id] ?? ['checked' => 0, 'my_checked' => 0, 'total' => $employees->count(), 'is_complete' => false];
                                $isActive = $currentJob && $currentJob->id === $job->id;
                                $cCount = $jobCommentsCount[$job->id] ?? 0;
                            @endphp
                            <a href="{{ route('wola.groups.assessments.index', [$group, 'assessor_id' => $currentAssessor->id, 'mode' => 'by_job', 'job_id' => $job->id]) }}"
                               class="p-3.5 rounded-2xl border transition text-left flex flex-col justify-between
                                   {{ $isActive ? 'border-blue-600 bg-blue-50/70 dark:bg-blue-900/30 shadow-md ring-2 ring-blue-500/20' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-slate-300' }}">
                                <div>
                                    <div class="flex items-center justify-between mb-1.5 min-h-[18px]">
                                        <div class="flex items-center space-x-1.5">
                                            @if($cCount > 0)
                                                <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-md bg-purple-50 text-purple-700 text-[10px] font-bold border border-purple-200" title="{{ $cCount }} catatan komentar">
                                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                                    <span>{{ $cCount }}</span>
                                                </span>
                                            @endif
                                        </div>
                                        @if($stat['is_complete'])
                                            <span class="w-4 h-4 rounded-full bg-emerald-500 text-white flex items-center justify-center text-[10px] font-bold">✓</span>
                                        @endif
                                    </div>
                                    <h4 class="font-bold text-xs text-slate-900 dark:text-white line-clamp-2 leading-tight">
                                        {{ $job->name }}
                                    </h4>
                                </div>
                                <div class="mt-3 pt-2 border-t border-slate-100 dark:border-slate-700/60 flex items-center justify-between text-[11px]">
                                    <span class="text-slate-500">Terisi</span>
                                    <span class="font-extrabold {{ $stat['is_complete'] ? 'text-emerald-600 dark:text-emerald-400' : 'text-blue-600 dark:text-blue-400' }}">
                                        {{ $stat['checked'] }}/{{ $stat['total'] }}
                                    </span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>

                @if($currentJob)
                    <!-- ========================================================= -->
                    <!-- KARTU INFORMASI JABATAN TERPILIH (Ringkas & Bersih) -->
                    <!-- ========================================================= -->
                    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <div class="flex items-center space-x-2">
                                <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-blue-100 text-blue-700 border border-blue-200">
                                    Posisi Jabatan Terpilih
                                </span>
                            </div>
                            <h3 class="text-xl font-black text-slate-900 dark:text-white mt-1">
                                {{ $currentJob->name }}
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Target Pengukuran: <strong>{{ $jobStats[$currentJob->id]['total'] }} Karyawan</strong> • Telah Diverifikasi: <strong>{{ $jobStats[$currentJob->id]['checked'] }} Unit</strong>
                            </p>
                        </div>

                        <div class="flex items-center space-x-2.5">
                            <!-- Tombol Pintas Scroll ke Bagian Komentar di Bawah -->
                            <a href="#commentSection"
                               class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition border bg-slate-50 hover:bg-blue-50 hover:text-blue-700 hover:border-blue-200 text-slate-700 border-slate-200 shadow-2xs">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                <span>Catatan & Komentar</span>
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-black bg-blue-600 text-white" x-text="comments.length"></span>
                            </a>
                        </div>
                    </div>

                    <!-- Toolbar Filter & Batch Checklist untuk Jabatan Ini -->
                    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-5 space-y-4">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <!-- Search Karyawan -->
                            <div class="w-full sm:w-72">
                                <div class="relative">
                                    <input type="text" x-model="searchQuery"
                                           placeholder="Cari NRP atau nama karyawan..."
                                           class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>

                            <!-- Filter Status & Batch Check Buttons -->
                            <div class="flex flex-wrap items-center gap-2">
                                <!-- Filter Status Pills -->
                                <div class="inline-flex p-1 bg-slate-100 dark:bg-slate-900 rounded-xl text-xs font-bold">
                                    <button type="button" @click="statusFilter = 'all'"
                                            :class="statusFilter === 'all' ? 'bg-white dark:bg-slate-800 text-blue-600 shadow-xs' : 'text-slate-500'"
                                            class="px-2.5 py-1 rounded-lg transition">Semua</button>
                                    <button type="button" @click="statusFilter = 'checked'"
                                            :class="statusFilter === 'checked' ? 'bg-white dark:bg-slate-800 text-emerald-600 shadow-xs' : 'text-slate-500'"
                                            class="px-2.5 py-1 rounded-lg transition">Sudah Dinilai</button>
                                    <button type="button" @click="statusFilter = 'unchecked'"
                                            :class="statusFilter === 'unchecked' ? 'bg-white dark:bg-slate-800 text-slate-700 shadow-xs' : 'text-slate-500'"
                                            class="px-2.5 py-1 rounded-lg transition">Belum Dinilai</button>
                                    <button type="button" @click="statusFilter = 'other'"
                                            :class="statusFilter === 'other' ? 'bg-white dark:bg-slate-800 text-amber-700 shadow-xs' : 'text-slate-500'"
                                            class="px-2.5 py-1 rounded-lg transition">Dinilai Lain</button>
                                </div>

                                <!-- Keterangan & Tombol Hapus Ceklis -->
                                @php
                                    $allEmployeeIdsJson = json_encode($employees->pluck('id'));
                                @endphp
                                <div class="flex items-center gap-2">
                                    <span class="hidden md:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-[11px] font-semibold text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                                        <svg class="w-3.5 h-3.5 text-blue-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                                        <span>Pilih karyawan & isi catatan di bawah untuk menilai</span>
                                    </span>
                                    <button type="button" @click="if(confirm('Batalkan seluruh centang penilaian Anda pada jabatan ini?')) { batchAction('uncheck_all', {{ $allEmployeeIdsJson }}, 'employee'); }"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-50 dark:bg-slate-900 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700 hover:bg-slate-100 text-xs font-bold rounded-xl transition"
                                            title="Membatalkan seluruh ceklis yang diinput oleh penilai ini">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        <span>Reset Ceklis Saya</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Daftar Checklist Karyawan -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-2">
                            @foreach($employees as $emp)
                                @php
                                    $empDataJson = json_encode([
                                        'id' => $emp->id,
                                        'fullname' => $emp->fullname,
                                        'nrp' => $emp->nrp,
                                    ]);
                                @endphp
                                <div class="p-4 rounded-xl border transition-all select-none flex items-center justify-between cursor-pointer"
                                     :class="{
                                         'ring-2 ring-blue-500 border-blue-500 shadow-md': (selectedEmployeeId === {{ $emp->id }}),
                                         'bg-blue-50/70 dark:bg-blue-900/20 border-blue-400 dark:border-blue-600 shadow-xs': isMyAssessment({{ $currentJob->id }}, {{ $emp->id }}),
                                         'bg-amber-50/30 dark:bg-amber-950/20 border-amber-300 dark:border-amber-700/80 hover:bg-amber-50/70 hover:border-amber-400': !isMyAssessment({{ $currentJob->id }}, {{ $emp->id }}) && isOtherAssessment({{ $currentJob->id }}, {{ $emp->id }}),
                                         'bg-white dark:bg-slate-900/60 border-slate-200 dark:border-slate-800 hover:border-blue-400 hover:bg-blue-50/20': !isMyAssessment({{ $currentJob->id }}, {{ $emp->id }}) && !isOtherAssessment({{ $currentJob->id }}, {{ $emp->id }})
                                     }"
                                     :title="isAssessed({{ $currentJob->id }}, {{ $emp->id }}) ? ('Sudah dinilai oleh: ' + getUnitAssessors({{ $currentJob->id }}, {{ $emp->id }}).names_string) : 'Klik untuk memilih & isi catatan evaluasi karyawan ini'"
                                     x-show="(statusFilter === 'all' 
                                         || (statusFilter === 'checked' && isAssessed({{ $currentJob->id }}, {{ $emp->id }})) 
                                         || (statusFilter === 'unchecked' && !isAssessed({{ $currentJob->id }}, {{ $emp->id }}))
                                         || (statusFilter === 'my' && isMyAssessment({{ $currentJob->id }}, {{ $emp->id }}))
                                         || (statusFilter === 'other' && isOtherAssessment({{ $currentJob->id }}, {{ $emp->id }}))
                                     ) && ('{{ strtolower($emp->fullname . ' ' . $emp->nrp) }}'.includes(searchQuery.toLowerCase()))"
                                     @click="handleEmployeeCardClick({{ $empDataJson }}, false)">
                                    
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center space-x-2">
                                            <span class="font-mono text-xs font-bold text-blue-600 dark:text-blue-400 shrink-0">{{ $emp->nrp }}</span>
                                            <span class="font-bold text-sm text-slate-900 dark:text-white truncate">{{ $emp->fullname }}</span>
                                            <template x-if="selectedEmployeeId === {{ $emp->id }}">
                                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-blue-600 text-white shadow-2xs">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
                                                    <span>Terpilih</span>
                                                </span>
                                            </template>
                                        </div>

                                        <!-- Tampilan nama-nama yang sudah menilai (jika 2, 3, atau 4 semua nama tampil) -->
                                        <template x-if="isAssessed({{ $currentJob->id }}, {{ $emp->id }})">
                                            <div class="mt-1 flex flex-wrap items-center gap-1">
                                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[11px] font-semibold transition"
                                                      :class="{
                                                          'bg-blue-100 text-blue-900 dark:bg-blue-950/80 dark:text-blue-200 border border-blue-300 dark:border-blue-700/60': isMyAssessment({{ $currentJob->id }}, {{ $emp->id }}),
                                                          'bg-amber-100 text-amber-950 dark:bg-amber-950/80 dark:text-amber-200 border border-amber-300 dark:border-amber-700/60': !isMyAssessment({{ $currentJob->id }}, {{ $emp->id }})
                                                      }"
                                                      :title="'Sudah dinilai oleh: ' + getUnitAssessors({{ $currentJob->id }}, {{ $emp->id }}).names_string">
                                                    <svg class="w-3.5 h-3.5 shrink-0" :class="isMyAssessment({{ $currentJob->id }}, {{ $emp->id }}) ? 'text-blue-600 dark:text-blue-400' : 'text-amber-600 dark:text-amber-400'" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                                    </svg>
                                                    <span class="font-normal text-slate-600 dark:text-slate-300">Sudah dinilai:</span>
                                                    <span class="font-bold" x-text="getUnitAssessors({{ $currentJob->id }}, {{ $emp->id }}).names_string"></span>
                                                </span>
                                            </div>
                                        </template>

                                        <p class="text-xs text-slate-500 mt-0.5 truncate">{{ $emp->dept }} • {{ $emp->section }} ({{ $emp->plant }})</p>
                                    </div>

                                    <!-- Status Badges -->
                                    <div class="shrink-0 ms-3">
                                        <!-- Badge Dinilai Penilai Aktif -->
                                        <template x-if="isMyAssessment({{ $currentJob->id }}, {{ $emp->id }})">
                                            <div class="flex flex-col items-end gap-1">
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-300 shadow-xs">
                                                    <svg class="w-3 h-3 text-blue-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                    <span>Dinilai Anda</span>
                                                </span>
                                                <button type="button"
                                                        @click.stop="if(confirm('Batalkan penilaian untuk ' + '{{ addslashes($emp->fullname) }}' + '?')) { toggleAssessment({{ $currentJob->id }}, {{ $emp->id }}, '{{ addslashes($emp->fullname) }}', '{{ addslashes($currentJob->name) }}', false); }"
                                                        class="text-[10px] font-bold text-red-500 hover:text-red-700 hover:underline inline-flex items-center gap-0.5 transition"
                                                        title="Batalkan penilaian untuk karyawan ini">
                                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                                    <span>Batal</span>
                                                </button>
                                                <template x-if="getUnitAssessors({{ $currentJob->id }}, {{ $emp->id }}).count > 1">
                                                    <span class="text-[9px] font-bold text-slate-500 dark:text-slate-400 text-right"
                                                          :title="'Total penilai: ' + getUnitAssessors({{ $currentJob->id }}, {{ $emp->id }}).names_string">
                                                        <span x-text="'+ ' + (getUnitAssessors({{ $currentJob->id }}, {{ $emp->id }}).count - 1) + ' penilai lain'"></span>
                                                    </span>
                                                </template>
                                            </div>
                                        </template>
                                        <!-- Badge Hanya Dinilai Orang Lain -->
                                        <template x-if="!isMyAssessment({{ $currentJob->id }}, {{ $emp->id }}) && isOtherAssessment({{ $currentJob->id }}, {{ $emp->id }})">
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-900 border border-amber-300 shadow-xs"
                                                  :title="'Sudah dinilai oleh: ' + getUnitAssessors({{ $currentJob->id }}, {{ $emp->id }}).names_string">
                                                <svg class="w-3 h-3 text-amber-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                                                <span x-text="getUnitAssessors({{ $currentJob->id }}, {{ $emp->id }}).count > 1 ? (getUnitAssessors({{ $currentJob->id }}, {{ $emp->id }}).count + ' Penilai') : 'Sudah Dinilai'"></span>
                                            </span>
                                        </template>
                                        <!-- Badge Belum Dinilai Siapa Pun -->
                                        <template x-if="!isAssessed({{ $currentJob->id }}, {{ $emp->id }})">
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                                                Belum
                                            </span>
                                        </template>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- ========================================================= -->
                    <!-- BAGIAN BAWAH: PANEL EVALUASI, CATATAN & KOMENTAR PENILAI -->
                    <!-- ========================================================= -->
                    <div id="commentSection" class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-6 space-y-5 mt-6">
                        <!-- Header Panel -->
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-4 border-b border-slate-100 dark:border-slate-700">
                            <div>
                                <div class="flex items-center space-x-2">
                                    <span class="px-2.5 py-1 rounded-lg text-xs font-black uppercase tracking-wider bg-blue-100 text-blue-700 dark:bg-blue-900/60 dark:text-blue-300 border border-blue-200 dark:border-blue-700 flex items-center space-x-1.5">
                                        <svg class="w-3.5 h-3.5 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 13V5a2 2 0 00-2-2H4a2 2 0 00-2 2v8a2 2 0 002 2h3l3 3 3-3h3a2 2 0 002-2zM5 7a1 1 0 011-1h8a1 1 0 110 2H6a1 1 0 01-1-1zm1 3a1 1 0 100 2h3a1 1 0 100-2H6z" clip-rule="evenodd"/></svg>
                                        <span>Catatan & Evaluasi Penilaian</span>
                                    </span>
                                </div>
                                <h3 class="text-lg font-black text-slate-900 dark:text-white mt-1.5 flex items-center gap-2">
                                    <span>Evaluasi Jabatan:</span>
                                    <span class="text-blue-600 underline">{{ $currentJob->name }}</span>
                                </h3>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    Karyawan yang dipilih pada daftar di atas otomatis menjadi sasaran komentar. Saat dikirim, data otomatis tersimpan di database dan spreadsheet serta ditandai <strong>Dinilai</strong>.
                                </p>
                            </div>

                            <div class="flex items-center space-x-2 self-start sm:self-auto">
                                <span class="text-xs font-bold px-3 py-1.5 rounded-xl bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200" x-text="comments.length + ' Catatan Tersimpan'"></span>
                            </div>
                        </div>

                        <!-- Target Karyawan yang Otomatis Dipilih -->
                        <div class="p-4 rounded-2xl bg-blue-50/70 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 space-y-3">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center font-black text-sm shadow-sm shrink-0">
                                        <span x-text="(selectedEmployeeName || 'K').substring(0, 2).toUpperCase()"></span>
                                    </div>
                                    <div>
                                        <div class="flex items-center space-x-2">
                                            <span class="text-[10px] font-black uppercase tracking-wider text-blue-600 dark:text-blue-400">
                                                Karyawan Terpilih yang Dikomentari:
                                            </span>
                                            <template x-if="newCommentEmployeeId && isMyAssessment(currentJobId, newCommentEmployeeId)">
                                                <span class="px-2 py-0.5 rounded-full text-[9px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                    ✓ Sudah Dinilai Anda
                                                </span>
                                            </template>
                                            <template x-if="newCommentEmployeeId && !isMyAssessment(currentJobId, newCommentEmployeeId)">
                                                <span class="px-2 py-0.5 rounded-full text-[9px] font-extrabold bg-amber-100 text-amber-800 border border-amber-200">
                                                    Belum Dinilai
                                                </span>
                                            </template>
                                        </div>
                                        <h4 class="text-sm font-black text-slate-900 dark:text-white" x-text="selectedEmployeeName ? (selectedEmployeeName + ' (' + selectedEmployeeNrp + ')') : 'Pilih karyawan di daftar atas'"></h4>
                                    </div>
                                </div>

                                <!-- Navigasi Cepat Karyawan Sebelumnya / Selanjutnya -->
                                <div class="flex items-center gap-1.5 self-end sm:self-auto">
                                    <button type="button" @click="selectPrevEmployee()"
                                            class="px-2.5 py-1.5 rounded-lg text-xs font-bold border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 shadow-2xs transition flex items-center space-x-1"
                                            title="Pilih karyawan sebelumnya">
                                        <span>‹ Sebelumnya</span>
                                    </button>
                                    <button type="button" @click="selectNextEmployee()"
                                            class="px-2.5 py-1.5 rounded-lg text-xs font-bold border border-blue-200 dark:border-blue-700 bg-white dark:bg-slate-800 hover:bg-blue-50 dark:hover:bg-slate-700 text-blue-700 dark:text-blue-300 shadow-2xs transition flex items-center space-x-1"
                                            title="Pilih karyawan berikutnya yang belum dinilai">
                                        <span>Selanjutnya ›</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Mode Switcher: Khusus Karyawan Ini vs Umum -->
                            <div class="flex flex-wrap items-center gap-2 pt-1 border-t border-blue-200/60 dark:border-blue-800/60 text-xs">
                                <span class="text-slate-500 font-medium">Mode Catatan:</span>
                                <button type="button" @click="setCommentMode('employee')"
                                        :class="newCommentEmployeeId ? 'bg-blue-600 text-white font-bold' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-100'"
                                        class="px-2.5 py-1 rounded-md text-[11px] transition shadow-2xs">
                                    Khusus Karyawan Terpilih (<span x-text="selectedEmployeeName || 'Dipilih'"></span>)
                                </button>
                                <button type="button" @click="setCommentMode('general')"
                                        :class="!newCommentEmployeeId ? 'bg-purple-600 text-white font-bold' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-100'"
                                        class="px-2.5 py-1 rounded-md text-[11px] transition shadow-2xs">
                                    Catatan Umum Seluruh Karyawan Jabatan Ini
                                </button>
                            </div>
                        </div>

                        <!-- Form Input Catatan Komentar -->
                        <div class="space-y-3">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                                <span class="font-bold text-slate-700 dark:text-slate-300">
                                    Tulis Catatan Evaluasi sebagai: <strong class="text-blue-600">{{ $currentAssessor->employee->fullname }}</strong>
                                    @if($currentAssessor->is_leader)
                                        <span class="text-[10px] font-extrabold text-blue-500">(Ketua Kelompok)</span>
                                    @endif
                                </span>
                                <span class="text-[11px] text-amber-600 dark:text-amber-400 font-semibold flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                                    <span>Catatan wajib diisi sebelum menyimpan penilaian</span>
                                </span>
                            </div>

                            <textarea id="commentInput"
                                      x-model="newCommentText"
                                      rows="3"
                                      :placeholder="newCommentEmployeeId 
                                          ? ('Tuliskan catatan evaluasi khusus untuk ' + selectedEmployeeName + ' (wajib diisi untuk menyimpan penilaian)...')
                                          : 'Tuliskan catatan umum evaluasi posisi jabatan ' + @json($currentJob->name) + '...'"
                                      class="w-full p-3.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-blue-500 transition resize-none"></textarea>

                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-1">
                                <div class="flex items-center space-x-2 text-xs text-slate-500">
                                    <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                    <span>Mengirim catatan ini otomatis menandai karyawan sebagai <strong>Dinilai</strong> di web & spreadsheet.</span>
                                </div>

                                <button type="button" @click="submitComment()"
                                        :disabled="isSubmittingComment"
                                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 active:scale-[0.99] text-white font-extrabold text-xs rounded-xl shadow-md shadow-blue-500/20 transition disabled:opacity-50 shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                                    <span x-text="isSubmittingComment ? 'Menyimpan...' : 'Kirim Catatan & Simpan Penilaian'"></span>
                                </button>
                            </div>
                        </div>

                        <!-- Riwayat Catatan yang Telah Diberikan untuk Jabatan Ini -->
                        <div class="pt-5 border-t border-slate-100 dark:border-slate-700/60 space-y-3">
                            <div class="flex items-center justify-between">
                                <h4 class="text-xs font-black uppercase tracking-wider text-slate-800 dark:text-slate-200 flex items-center space-x-2">
                                    <span>Riwayat Catatan Penilai:</span>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300" x-text="comments.length"></span>
                                </h4>
                            </div>

                            <div class="space-y-3 max-h-80 overflow-y-auto p-1 scrollbar-thin">
                                <template x-if="comments.length === 0">
                                    <div class="p-6 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-center text-xs text-slate-500">
                                        Belum ada catatan evaluasi untuk jabatan ini. Pilih karyawan di atas dan tuliskan catatan evaluasi pertama.
                                    </div>
                                </template>

                                <template x-for="c in comments" :key="c.id">
                                    <div class="p-4 rounded-2xl border transition-all"
                                         :class="c.is_leader ? 'bg-blue-50/50 border-blue-200 dark:bg-blue-950/20 dark:border-blue-800' : 'bg-slate-50/70 border-slate-200 dark:bg-slate-900/50 dark:border-slate-800'">
                                        <div class="flex items-start justify-between gap-3 mb-2">
                                            <div class="flex items-center space-x-2.5">
                                                <div class="w-8 h-8 rounded-xl flex items-center justify-center font-black text-xs shrink-0 shadow-xs"
                                                     :class="c.is_leader ? 'bg-blue-600 text-white' : 'bg-slate-700 text-white'">
                                                    <span x-text="(c.assessor_name || 'Penilai').substring(0, 2).toUpperCase()"></span>
                                                </div>
                                                <div>
                                                    <div class="flex items-center space-x-2">
                                                        <span class="font-extrabold text-xs text-slate-900 dark:text-white" x-text="c.assessor_name || 'Penilai'"></span>
                                                        <template x-if="c.is_leader">
                                                            <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase bg-blue-100 text-blue-800 border border-blue-200">
                                                                Ketua Kelompok
                                                            </span>
                                                        </template>
                                                        <template x-if="!c.is_leader">
                                                            <span class="px-2 py-0.5 rounded-full text-[9px] font-bold uppercase bg-slate-200 text-slate-700">
                                                                Anggota Penilai
                                                            </span>
                                                        </template>
                                                    </div>
                                                    <div class="flex items-center space-x-2 text-[10px] text-slate-400 mt-0.5">
                                                        <span x-text="c.created_at_formatted"></span>
                                                        <span>•</span>
                                                        <span x-text="c.created_at_human"></span>
                                                    </div>
                                                </div>
                                            </div>

                                            <template x-if="c.can_delete">
                                                <button type="button" @click="deleteComment(c.id)"
                                                        class="text-slate-400 hover:text-rose-600 p-1.5 rounded-lg hover:bg-rose-50 transition"
                                                        title="Hapus catatan ini">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </template>
                                        </div>

                                        <!-- Badge Nama Karyawan yang Dikomentari -->
                                        <div class="pl-10 mb-2">
                                            <template x-if="c.target_employee_name">
                                                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-blue-100/90 dark:bg-blue-900/50 border border-blue-300 dark:border-blue-700 text-blue-900 dark:text-blue-100 text-[11px] font-bold">
                                                    <svg class="w-3.5 h-3.5 text-blue-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg>
                                                    <span>Karyawan Sasaran:</span>
                                                    <span class="font-extrabold underline" x-text="c.target_employee_name"></span>
                                                    <span class="text-[10px] text-blue-700 dark:text-blue-300 font-mono" x-show="c.target_employee_nrp" x-text="'(' + c.target_employee_nrp + ')'"></span>
                                                </div>
                                            </template>
                                            <template x-if="!c.target_employee_name">
                                                <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-lg bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 text-[10px] font-semibold">
                                                    <svg class="w-3 h-3 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                                    <span>Catatan Umum (Seluruh Karyawan Jabatan Ini)</span>
                                                </div>
                                            </template>
                                        </div>

                                        <div class="text-xs text-slate-800 dark:text-slate-200 leading-relaxed whitespace-pre-line pl-10" x-text="c.comment"></div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                @endif
            @endif

            <!-- ============================================================= -->
            <!-- MODE 2: PER KARYAWAN -->
            <!-- ============================================================= -->
            @if($mode === 'by_employee')
                <!-- Selector Karyawan yang Dinilai -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-6">
                    <label class="block text-xs font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-2">
                        Pilih Karyawan Sasaran yang Ingin Dinilai:
                    </label>

                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 max-h-56 overflow-y-auto p-1">
                        @foreach($employees as $emp)
                            @php
                                $stat = $employeeStats[$emp->id] ?? ['checked' => 0, 'my_checked' => 0, 'total' => $jobs->count(), 'is_complete' => false];
                                $isActive = $currentEmployee && $currentEmployee->id === $emp->id;
                            @endphp
                            <a href="{{ route('wola.groups.assessments.index', [$group, 'assessor_id' => $currentAssessor->id, 'mode' => 'by_employee', 'employee_id' => $emp->id]) }}"
                               class="p-3 rounded-xl border text-left flex items-center justify-between transition
                                   {{ $isActive ? 'border-blue-600 bg-blue-50/70 dark:bg-blue-900/30 shadow-sm ring-2 ring-blue-500/20' : 'border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/40 hover:border-slate-300' }}">
                                <div>
                                    <span class="font-mono text-xs font-bold text-blue-600 dark:text-blue-400">{{ $emp->nrp }}</span>
                                    <h4 class="font-bold text-xs text-slate-900 dark:text-white line-clamp-1">{{ $emp->fullname }}</h4>
                                </div>
                                <div class="text-right shrink-0 ms-2">
                                    <span class="text-xs font-extrabold {{ $stat['is_complete'] ? 'text-emerald-600' : 'text-slate-600 dark:text-slate-400' }}">
                                        {{ $stat['checked'] }}/{{ $stat['total'] }}
                                    </span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>

                @if($currentEmployee)
                    <!-- Checklist Jabatan untuk Karyawan Terpilih -->
                    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-5 space-y-4">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 border-b border-slate-100 dark:border-slate-700/60">
                            <div>
                                <span class="text-xs uppercase font-bold text-slate-400">Menilai Jabatan untuk:</span>
                                <h3 class="text-base font-extrabold text-slate-900 dark:text-white mt-0.5">
                                    {{ $currentEmployee->fullname }} <span class="font-mono text-sm text-blue-600">({{ $currentEmployee->nrp }})</span>
                                </h3>
                                <p class="text-xs text-slate-500">{{ $currentEmployee->dept }} • {{ $currentEmployee->section }}</p>
                            </div>

                            <!-- Filter Status & Batch Check Buttons -->
                            <div class="flex flex-wrap items-center gap-2">
                                <div class="inline-flex p-1 bg-slate-100 dark:bg-slate-900 rounded-xl text-xs font-bold">
                                    <button type="button" @click="statusFilter = 'all'"
                                            :class="statusFilter === 'all' ? 'bg-white dark:bg-slate-800 text-blue-600 shadow-xs' : 'text-slate-500'"
                                            class="px-2.5 py-1 rounded-lg transition">Semua</button>
                                    <button type="button" @click="statusFilter = 'checked'"
                                            :class="statusFilter === 'checked' ? 'bg-white dark:bg-slate-800 text-emerald-600 shadow-xs' : 'text-slate-500'"
                                            class="px-2.5 py-1 rounded-lg transition">Sudah</button>
                                    <button type="button" @click="statusFilter = 'unchecked'"
                                            :class="statusFilter === 'unchecked' ? 'bg-white dark:bg-slate-800 text-slate-700 shadow-xs' : 'text-slate-500'"
                                            class="px-2.5 py-1 rounded-lg transition">Belum</button>
                                </div>

                                @php
                                    $allJobIdsJson = json_encode($jobs->pluck('id'));
                                @endphp
                                <button type="button" @click="if(confirm('Batalkan seluruh centang penilaian Anda untuk karyawan ini?')) { batchAction('uncheck_all', {{ $allJobIdsJson }}, 'job'); }"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-50 dark:bg-slate-900 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700 hover:bg-slate-100 text-xs font-bold rounded-xl transition"
                                        title="Hanya membatalkan ceklis yang diinput oleh penilai ini">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    <span>Hapus Ceklis Saya</span>
                                </button>
                            </div>
                        </div>

                        <!-- Daftar Checklist Jabatan -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            @foreach($jobs as $job)
                                <div class="p-4 rounded-xl border transition-all select-none flex items-center justify-between cursor-pointer"
                                     :class="{
                                         'bg-blue-50/70 dark:bg-blue-900/20 border-blue-400 dark:border-blue-600 shadow-xs': isMyAssessment({{ $job->id }}, {{ $currentEmployee->id }}),
                                         'bg-amber-50/30 dark:bg-amber-950/20 border-amber-300 dark:border-amber-700/80 hover:bg-amber-50/70 hover:border-amber-400': !isMyAssessment({{ $job->id }}, {{ $currentEmployee->id }}) && isOtherAssessment({{ $job->id }}, {{ $currentEmployee->id }}),
                                         'bg-white dark:bg-slate-900/60 border-slate-200 dark:border-slate-800 hover:border-blue-400 hover:bg-blue-50/20': !isMyAssessment({{ $job->id }}, {{ $currentEmployee->id }}) && !isOtherAssessment({{ $job->id }}, {{ $currentEmployee->id }})
                                     }"
                                     :title="isAssessed({{ $job->id }}, {{ $currentEmployee->id }}) ? ('Sudah dinilai oleh: ' + getUnitAssessors({{ $job->id }}, {{ $currentEmployee->id }}).names_string) : 'Klik untuk menilai jabatan ini'"
                                     x-show="(statusFilter === 'all' 
                                         || (statusFilter === 'checked' && isAssessed({{ $job->id }}, {{ $currentEmployee->id }})) 
                                         || (statusFilter === 'unchecked' && !isAssessed({{ $job->id }}, {{ $currentEmployee->id }}))
                                     )"
                                     @click="handleCardClick({{ $job->id }}, {{ $currentEmployee->id }}, '{{ addslashes($currentEmployee->fullname) }}', '{{ addslashes($job->name) }}')">
                                    
                                    <div class="min-w-0 flex-1">
                                        <h4 class="font-bold text-sm text-slate-900 dark:text-white truncate">{{ $job->name }}</h4>

                                        <!-- Tampilan nama-nama yang sudah menilai (jika 2, 3, atau 4 semua nama tampil) -->
                                        <template x-if="isAssessed({{ $job->id }}, {{ $currentEmployee->id }})">
                                            <div class="mt-1 flex flex-wrap items-center gap-1">
                                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[11px] font-semibold transition"
                                                      :class="{
                                                          'bg-blue-100 text-blue-900 dark:bg-blue-950/80 dark:text-blue-200 border border-blue-300 dark:border-blue-700/60': isMyAssessment({{ $job->id }}, {{ $currentEmployee->id }}),
                                                          'bg-amber-100 text-amber-950 dark:bg-amber-950/80 dark:text-amber-200 border border-amber-300 dark:border-amber-700/60': !isMyAssessment({{ $job->id }}, {{ $currentEmployee->id }})
                                                      }"
                                                      :title="'Sudah dinilai oleh: ' + getUnitAssessors({{ $job->id }}, {{ $currentEmployee->id }}).names_string">
                                                    <svg class="w-3.5 h-3.5 shrink-0" :class="isMyAssessment({{ $job->id }}, {{ $currentEmployee->id }}) ? 'text-blue-600 dark:text-blue-400' : 'text-amber-600 dark:text-amber-400'" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                                    </svg>
                                                    <span class="font-normal text-slate-600 dark:text-slate-300">Sudah dinilai:</span>
                                                    <span class="font-bold" x-text="getUnitAssessors({{ $job->id }}, {{ $currentEmployee->id }}).names_string"></span>
                                                </span>
                                            </div>
                                        </template>
                                    </div>

                                    <!-- Status Badges -->
                                    <div class="shrink-0 ms-3">
                                        <!-- Badge Dinilai Penilai Aktif -->
                                        <template x-if="isMyAssessment({{ $job->id }}, {{ $currentEmployee->id }})">
                                            <div class="flex flex-col items-end gap-0.5">
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-300 shadow-xs">
                                                    <svg class="w-3 h-3 text-blue-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                    <span>Dinilai Anda</span>
                                                </span>
                                                <template x-if="getUnitAssessors({{ $job->id }}, {{ $currentEmployee->id }}).count > 1">
                                                    <span class="text-[9px] font-bold text-slate-500 dark:text-slate-400 text-right"
                                                          :title="'Total penilai: ' + getUnitAssessors({{ $job->id }}, {{ $currentEmployee->id }}).names_string">
                                                        <span x-text="'+ ' + (getUnitAssessors({{ $job->id }}, {{ $currentEmployee->id }}).count - 1) + ' penilai lain'"></span>
                                                    </span>
                                                </template>
                                            </div>
                                        </template>
                                        <!-- Badge Hanya Dinilai Orang Lain -->
                                        <template x-if="!isMyAssessment({{ $job->id }}, {{ $currentEmployee->id }}) && isOtherAssessment({{ $job->id }}, {{ $currentEmployee->id }})">
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-900 border border-amber-300 shadow-xs"
                                                  :title="'Sudah dinilai oleh: ' + getUnitAssessors({{ $job->id }}, {{ $currentEmployee->id }}).names_string">
                                                <svg class="w-3 h-3 text-amber-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                                                <span x-text="getUnitAssessors({{ $job->id }}, {{ $currentEmployee->id }}).count > 1 ? (getUnitAssessors({{ $job->id }}, {{ $currentEmployee->id }}).count + ' Penilai') : 'Sudah Dinilai'"></span>
                                            </span>
                                        </template>
                                        <!-- Badge Belum Dinilai Siapa Pun -->
                                        <template x-if="!isAssessed({{ $job->id }}, {{ $currentEmployee->id }})">
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                                                Belum
                                            </span>
                                        </template>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endif

        </div>
        <!-- Floating Toast Pop-up Notification -->
        <div x-show="toastNotice.show"
             x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-2"
             class="fixed bottom-6 right-6 z-50 max-w-md p-4 rounded-2xl shadow-xl border bg-slate-900 text-white flex items-center space-x-3"
             style="display: none;">
            <span class="p-1.5 rounded-xl shrink-0"
                  :class="toastNotice.type === 'success' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-amber-500/20 text-amber-400'">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </span>
            <div class="flex-1 text-xs font-semibold leading-relaxed" x-text="toastNotice.message"></div>
            <button type="button" @click="toastNotice.show = false" class="text-slate-400 hover:text-white p-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

    </div>
</x-app-layout>
