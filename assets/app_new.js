$(document).ready(function () {
    // Fungsi untuk mengecek session_expired Start
    const idleTimeout = 1200000; // 20 menit
    const warningTimeout = 1140000; // 19 menit (1 menit sebelum kedaluwarsa)
    let idleTimer;
    let warningTimer;
    let sessionExtended = false;

    // Sinkronisasi antar tab menggunakan localStorage
    function updateLastActivity() {
        localStorage.setItem('lastActivity', Date.now());
        resetIdleTimer();
    }

    // Tampilkan peringatan sebelum sesi kedaluwarsa
    function showWarning() {
        Swal.fire({
            icon: "warning",
            title: "Session About to Expire",
            text: "Your session will expire in 1 minute. Do you want to extend your session?",
            showCancelButton: true,
            confirmButtonText: "Yes, Extend Session",
            cancelButtonText: "No, Logout",
            timer: 60000, // Peringatan akan hilang setelah 1 menit
            timerProgressBar: true,
        }).then((result) => {
            if (result.isConfirmed) {
                // Perpanjang sesi dengan mengirimkan request ke server
                $.ajax({
                    url: "/extend_session",
                    type: "POST",
                    success: function (response) {
                        if (response.status === "extended") {
                            sessionExtended = true;
                            updateLastActivity();
                            Swal.fire({
                                icon: "success",
                                title: "Session Extended",
                                text: "Your session has been extended.",
                                timer: 2000,
                                showConfirmButton: false,
                            });
                        }
                    },
                    error: function () {
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: "Failed to extend session. Please login again.",
                        }).then(() => {
                            window.location.href = "/login?message=session_expired";
                        });
                    },
                });
            } else {
                // Logout jika pengguna memilih "No, Logout"
                clearTimeout(idleTimer);
                Swal.fire({
                    icon: "warning",
                    title: "Session Time Expired",
                    text: "Please login again.",
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: true,
                    confirmButtonText: "Login Again",
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "/login?message=session_expired";
                    }
                });
            }
        });
    }

    // Reset timer setiap kali ada aktivitas pengguna
    function resetIdleTimer() {
        clearTimeout(idleTimer);
        clearTimeout(warningTimer);
        sessionExtended = false;
        warningTimer = setTimeout(() => {
            if (!sessionExtended) {
                showWarning();
            }
        }, warningTimeout);
        idleTimer = setTimeout(() => {
            if (!sessionExtended) {
                clearTimeout(warningTimer);
                Swal.fire({
                    icon: "warning",
                    title: "Session Time Expired",
                    text: "Please login again.",
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: true,
                    confirmButtonText: "Login Again",
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "/login?message=session_expired";
                    }
                });
            }
        }, idleTimeout);
    }

    // Event yang dianggap sebagai aktivitas pengguna
    $(document).on("mousemove keydown click", function () {
        updateLastActivity();
    });

    // Sinkronisasi antar tab
    setInterval(() => {
        const lastActivity = localStorage.getItem('lastActivity');
        if (lastActivity && (Date.now() - lastActivity) < idleTimeout) {
            resetIdleTimer();
        }
    }, 1000);

    resetIdleTimer();

    setInterval(function () {
        $.ajax({
            url: "/check_session",
            type: "GET",
            success: function (response) {
                if (response.status === "expired") {
                    clearTimeout(idleTimer);
                    clearTimeout(warningTimer);
                    Swal.fire({
                        icon: "warning",
                        title: "Session Time Expired",
                        text: "Please login again.",
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: true,
                        confirmButtonText: "Login Again",
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = "/login?message=session_expired";
                        }
                    });
                }
            },
            error: function () {
                console.error("Gagal memeriksa sesi di server");
            },
        });
    }, 60000); // Fungsi untuk mengecek session_expired End

    // Fungsi untuk menentukan halaman saat ini
    function getCurrentPage() {
        return window.location.pathname.split("/").pop();
    }

    const currentPage = getCurrentPage();
    let newTaskTable = null;
    let completedTaskTable = null;
    let reportTaskTable = null;
    let itemsTable = null;
    let orderReqDetailTable = null; // Variabel global untuk OrderReq_Detail
    let currentOrderNumber = null;

    // Fungsi untuk menambahkan class 'active' ke menu
    function setActiveMenu() {
        const currentPage = getCurrentPage();
        $(".sidebar ul li a").each(function () {
            const menuHref = $(this).attr("href");
            if (menuHref && menuHref.includes(currentPage)) {
                $(this).parent().addClass("active");
            } else {
                $(this).parent().removeClass("active");
            }
        });
    }
    setActiveMenu();

    // Event handler untuk sidebar
    $(".sidebar ul li").on("click", function () {
        $(".sidebar ul li.active").removeClass("active");
        $(this).addClass("active");
    });

    $(".open-btn").on("click", function () {
        $(".sidebar").addClass("active");
    });

    $(".close-btn").on("click", function () {
        $(".sidebar").removeClass("active");
    });

    $(document).on("click", function (e) {
        if (window.innerWidth <= 767) {
            if (
                !$(e.target).closest(".sidebar").length &&
                !$(e.target).closest(".open-btn").length &&
                $(".sidebar").hasClass("active")
            ) {
                $(".sidebar").removeClass("active");
            }
        }
    });

    $(".sidebar").on("click", function (e) {
        e.stopPropagation();
    });

    // Fungsi global untuk menampilkan loading spinner
    function showLoading() {
        console.log("showLoading called");
        const overlay = document.getElementById("loadingOverlay");
        if (overlay) {
            overlay.classList.add("show");
        } else {
            console.error("Loading overlay not found");
        }
    }

    // Fungsi global untuk menyembunyikan loading spinner
    function hideLoading() {
        console.log("hideLoading called");
        const overlay = document.getElementById("loadingOverlay");
        if (overlay) {
            overlay.classList.remove("show");
        } else {
            console.error("Loading overlay not found");
        }
    }

    // Fungsi untuk inisialisasi DataTables
    function initializeDataTable() {
        if (currentPage === "NewTask.php") {
            if (!newTaskTable) {
                newTaskTable = $("#tabelNewTask").DataTable({
                    ajax: {
                        url: "../API/Fetch_NewTask.php",
                        dataSrc: "",
                    },
                    columns: [
                        {
                            data: null,
                            render: function (data, type, row, meta) {
                                return meta.row + meta.settings._iDisplayStart + 1;
                            },
                        },
                        { data: "order_number" },
                        { data: "site_id" },
                        { data: "site_name" },
                        { data: "customer" },
                        { data: "destination" },
                        { data: "created_at" },
                        { data: "created_by" },
                        { data: "status" },
                        {
                            data: null,
                            render: function (data, type, row) {
                                return `
                                    <button class="btn btn-primary btn-sm edit-btn" data-id="${row.id}" data-order-number="${row.order_number}" data-destination="${row.destination}">
                                        <i class="fa-regular fa-plus"></i>
                                    </button>
                                `;
                            },
                            orderable: false,
                        },
                    ],
                    order: [[0, "asc"]],
                    language: {
                        url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json",
                    },
                    scrollX: true,
                    fixedColumns: {
                        leftColumns: 0,
                        rightColumns: 1,
                    },
                    preDrawCallback: function () {
                        showLoading();
                    },
                    drawCallback: function () {
                        hideLoading();
                    }
                });
            }
        } else if (currentPage === "Completed.php") {
            if (!completedTaskTable) {
                completedTaskTable = $("#tabelCompletedTask").DataTable({
                    ajax: {
                        url: "../API/Fetch_CompletedTask.php",
                        dataSrc: "",
                    },
                    columns: [
                        {
                            data: null,
                            render: function (data, type, row, meta) {
                                return meta.row + meta.settings._iDisplayStart + 1;
                            },
                        },
                        { data: "order_number" },
                        { data: "site_id" },
                        { data: "site_name" },
                        { data: "customer" },
                        { data: "destination" },
                        { data: "created_at" },
                        { data: "status" },
                        {
                            data: null,
                            render: function (data, type, row) {
                                // pengekan Role Jika Customer Tidak Bisa Action Edit Start
                                let buttons = `
                            
                            <a href="../modules/print_delivery_order.php?order_number=${row.order_number}" target="_blank" class="btn btn-warning btn-sm ms-1">
                                <i class="fas fa-print"></i>Print</a>
                        `;
                        // Hanya tambahkan tombol Edit jika peran bukan customer
                        if (userRole !== "customer") {
                            buttons = `
                                <button class="btn btn-sm edit-items-btn" data-order-number="${row.order_number}" data-destination="${row.destination}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm view-items-btn" data-order-number="${row.order_number}">
                                <i class="fas fa-eye"></i>
                            </button>
                            ` + buttons;
                        }
                        return buttons;
                    },
                    orderable: false,
                },
                ],  // pengekan Role Jika Customer Tidak Bisa Action Edit End
                    order: [[0, "asc"]],
                    language: {
                        url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json",
                    },
                    scrollX: true,
                    fixedColumns: {
                        leftColumns: 0,
                        rightColumns: 1,
                    },
                    preDrawCallback: function () {
                        showLoading();
                    },
                    drawCallback: function () {
                        hideLoading();
                    }
                });
            }
        } else if (currentPage === "ReportTask.php") {
            if (!reportTaskTable) {
                reportTaskTable = $("#tabelReportTask").DataTable({
                    ajax: {
                        url: "../API/Fetch_ReportTask.php",
                        dataSrc: "data",
                    },
                    columns: [
                        {
                            data: null,
                            render: function (data, type, row, meta) {
                                return meta.row + meta.settings._iDisplayStart + 1;
                            },
                        },
                        { data: "order_number" },
                        { data: "destination" },
                        { data: "driver_name" },
                        { data: "qty" },
                        {
                            data: "total_cbm",
                            render: function (data, type, row) {
                                return parseFloat(data).toFixed(2);
                            },
                        },
                        { data: "date_pickup" },
                        { data: "submit_by" },
                    ],
                    order: [[0, "asc"]],
                    language: {
                        url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json",
                    },
                    scrollX: true,
                });
            }
        } else if (currentPage === "OrderReq_Detail.php") {
            if (!orderReqDetailTable) {
                orderReqDetailTable = $("#OrderReq_Detail").DataTable({
                    ajax: {
                        url: "../API/Fetch_OrderReq_Detail.php",
                        dataSrc: ""
                    },
                    columns: [
                        {
                            data: null,
                            render: function (data, type, row, meta) {
                                return meta.row + meta.settings._iDisplayStart + 1;
                            }
                        },
                        { data: "order_number" },
                        { data: "site_id" },
                        { data: "site_name" },
                        { data: "destination" },
                        { data: "status" },
                        {
                            data: null,
                            render: function (data, type, row) {
                                return `
                                    <button class="btn btn-sm delete-btn" data-id="${row.id}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                `;
                            },
                            orderable: false,
                        },
                    ],
                    order: [[5, "asc"]],
                    language: {
                        url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
                    },
                    scrollX: true,
                    fixedColumns: {
                    leftColumns: 0,
                    rightColumns: 1
                    },
                    preDrawCallback: function () {
                    showLoading();
                },
                drawCallback: function () {
                    hideLoading();
                }
                });
            }
        }
    }
    initializeDataTable();

    // Handle export Excel
    $("#exportTask").on("click", function () {
        if (currentPage === "NewTask.php") {
            window.location.href = "../modules/export_tasks.php";
        } else if (currentPage === "Completed.php") {
            window.location.href = "../modules/export_completed_tasks.php";
        } else if (currentPage === "ReportTask.php") {
            window.location.href = "../modules/export_report_tasks.php";
        }
    });

    // Logika khusus untuk NewTask.php
    if (currentPage === "NewTask.php") {
        // Handle submit form NewTask
        $("#NewTaskForm").on("submit", function (e) {
            e.preventDefault();
            let submitBtn = $(this).find('button[type="submit"]');

            $.ajax({
                url: "../modules/Proses_NewTask.php",
                type: "POST",
                data: $(this).serialize(),
                dataType: "json",
                beforeSend: function () {
                    showLoading();
                    submitBtn.prop("disabled", true).text("Saving...");
                },
                success: function (response) {
                    hideLoading();
                    submitBtn.prop("disabled", false).text("Simpan Data");
                    if (response.status === "success") {
                        Swal.fire({
                            icon: "success",
                            title: "success",
                            text: response.message || "Task berhasil dibuat!",
                            showConfirmButton: true,
                            timer: 3000
                        }).then(() => {
                            $("#NewTaskModal").modal("hide");
                            $("#NewTaskForm")[0].reset();
                            if (newTaskTable) {
                                newTaskTable.ajax.reload();
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Gagal",
                            text: response.message || "Terjadi kesalahan saat membuat task!"
                        });
                    }
                },
                error: function (xhr) {
                    hideLoading();
                    submitBtn.prop("disabled", false).text("Simpan Data");
                    let response;
                    try {
                        response = JSON.parse(xhr.responseText);
                    } catch (e) {
                        response = {
                            status: "error",
                            message: "Terjadi kesalahan tidak terduga!"
                        };
                    }
                    Swal.fire({
                        icon: "error",
                        title: "Gagal",
                        text: response.message || "Terjadi kesalahan pada server!"
                    });
                }
            });
        });
        $("#NewTaskModal").on("hidden.bs.modal", function () {
            $("#NewTaskForm")[0].reset();
        });

        // Handle submit bulk upload
        $("#bulkNewTaskForm").on("submit", function (e) {
            e.preventDefault();
            let submitBtn = $(this).find('button[type="submit"]');
            let formData = new FormData(this);

            $.ajax({
                url: "../modules/Proses_BulkNewTask.php",
                type: "POST",
                data: formData,
                dataType: "json",
                contentType: false,
                processData: false,
                beforeSend: function () {
                    showLoading();
                    submitBtn.prop("disabled", true).text("Uploading...");
                },
                success: function (response) {
                    hideLoading();
                    submitBtn.prop("disabled", false).text("Unggah");
                    if (response.status === "success" || response.status === "warning") {
                        Swal.fire({
                            icon: response.status === "success" ? "success" : "warning",
                            title: response.status === "success" ? "success" : "Peringatan",
                            text: response.message || "Upload task selesai!",
                            showConfirmButton: true,
                            timer: 3000
                        }).then(() => {
                            $("#bulkNewTaskModal").modal("hide");
                            $("#bulkNewTaskForm")[0].reset();
                            if (newTaskTable) {
                                newTaskTable.ajax.reload();
                            }
                            if (response.errors && response.errors.length > 0) {
                                response.errors.forEach(function (error) {
                                    Swal.fire({
                                        icon: "error",
                                        title: "Kesalahan",
                                        text: error
                                    });
                                });
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Gagal",
                            text: response.message || "Terjadi kesalahan saat upload!"
                        });
                    }
                },
                error: function (xhr) {
                    hideLoading();
                    submitBtn.prop("disabled", false).text("Unggah");
                    let response;
                    try {
                        response = JSON.parse(xhr.responseText);
                    } catch (e) {
                        response = {
                            status: "error",
                            message: "Terjadi kesalahan tidak terduga!"
                        };
                    }
                    Swal.fire({
                        icon: "error",
                        title: "Gagal",
                        text: response.message || "Terjadi kesalahan pada server!"
                    });
                }
            });
        });
        // Tambahkan handler reset untuk bulkNewTaskModal di sini
        $("#bulkNewTaskModal").on("hidden.bs.modal", function () {
            $("#bulkNewTaskForm")[0].reset();
        });
    }

    // Fungsi untuk menginisialisasi Select2
    function initializeSelect2($element, dropdownParent) {
        $element.select2({
            placeholder: "Pilih Item Code",
            allowClear: true,
            width: "100%",
            minimumInputLength: 0,
            minimumResultsForSearch: 0,
            dropdownParent: $(dropdownParent),
            language: {
                noResults: function () {
                    return "Data tidak ditemukan";
                },
                searching: function () {
                    return "Mencari...";
                },
            },
            dropdownAutoWidth: true,
            dropdownCssClass: "select2-dropdown-position-fix",
            ajax: {
                url: "../API/Fetch_Materials.php",
                dataType: "json",
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term || ""
                    };
                },
                processResults: function (data) {
                    console.log("Data dari server:", data);
                    return {
                        results: data.results
                    };
                },
                cache: true
            }
        });

        $element.on("select2:open", function () {
            $(".select2-search__field").attr(
                "placeholder",
                "Ketik Disini Untuk Mencari..!"
            );
        });

        console.log("Select2 diinisialisasi pada:", $element);

        $element.on("select2:open", function () {
            console.log("Dropdown Select2 dibuka");
            const searchField = $(".select2-search__field");
            if (searchField.length) {
                console.log("Kolom pencarian ditemukan:", searchField);
                searchField.focus();
            } else {
                console.log("Kolom pencarian TIDAK ditemukan");
            }
        });
    }

    // Fungsi untuk menambahkan baris item baru
    function addItemRow(selectedItem = "") {
        const itemRow = `
            <tr class="item-row">
                <td>
                    <select name="item_name[]" class="form-control select2-item" required>
                        <option value="">Pilih Item Code</option>
                        ${selectedItem ? `<option value="${selectedItem}" selected>${selectedItem}</option>` : ""}
                    </select>
                </td>
                <td>
                    <input type="number" name="qty[]" class="form-control" required>
                </td>
                <td>
                    <input type="text" name="uom[]" class="form-control uom" readonly required>
                </td>
                <td>
                    <input type="text" name="dimension[]" class="form-control dimensi" readonly required>
                </td>
                
                <td>
                    <button type="button" class="btn btn-sm remove-item">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        $("#itemsContainer").append(itemRow);

        const $newSelect = $("#itemsContainer").find(".select2-item").last();
        initializeSelect2($newSelect, "#addItemModal");

        if (selectedItem) {
            $newSelect.val(selectedItem).trigger("change");
        }
    }

    // Event handler untuk mengisi dimensi otomatis saat item_name dipilih
    $(document).on("change", ".select2-item", function () {
        const $row = $(this).closest(".item-row");
        const itemName = $(this).val();
        const $dimensiInput = $row.find(".dimensi");
        const $uomInput = $row.find(".uom"); 

        if (itemName) {
            $.ajax({
                url: "../API/Fetch_Dimensi.php",
                type: "GET",
                data: { item_name: itemName },
                dataType: "json",
                beforeSend: function () {
                    showLoading();
                    $dimensiInput.val("Loading...");
                    $uomInput.val("Loading...");
                },
                success: function (response) {
                    hideLoading();
                    if (response.dimensi) {
                        $dimensiInput.val(response.dimensi);
                    } else {
                        $dimensiInput.val("");
                        
                    }
                    if (response.uom) {
                        $uomInput.val(response.uom);
                    } else {
                        $uomInput.val("");
                    }
                    if (!response.dimensi || !response.uom) {
                        Swal.fire({
                            icon: "warning",
                            title: "Dimensi Tidak Ditemukan",
                            text: `Dimensi untuk item ${itemName} tidak ditemukan.`,
                            confirmButtonText: "OK"
                        });
                    }
                },
                error: function () {
                    hideLoading();
                    $dimensiInput.val("");
                    Swal.fire({
                        icon: "error",
                        title: "Gagal",
                        text: "Gagal mengambil data dimensi dari server.",
                        confirmButtonText: "OK"
                    });
                }
            });
        } else {
            $dimensiInput.val("");
            $uomInput.val("");
        }
    });

    // Handle klik tombol Tambah Item
    $("#addItemRow").on("click", function () {
        addItemRow();
    });

    // Handle klik tombol Hapus item
    $(document).on("click", ".remove-item", function () {
        const $row = $(this).closest(".item-row");
        $row.find(".select2-item").select2("destroy");
        $row.remove();
    });

    // Handle klik ikon pensil untuk menambah item (NewTask.php)
    $(document).on("click", ".edit-btn", function () {
        if (currentPage === "NewTask.php") {
            const orderNumber = $(this).data("order-number");
            const destination = $(this).data("destination");
            console.log(
                "Tambah Item - Order Number:",
                orderNumber,
                "Destination:",
                destination !== undefined ? destination : "UNDEFINED"
            );
            $("#order_number").val(orderNumber);
            $("#destination").val(destination);
            $("#addItemModalLabel").text("SDR Number : " + orderNumber);
            $("#itemsContainer").empty();
            addItemRow();
            $("#addItemForm").attr("action", "../modules/Proses_AddItems.php");
            $("#addItemModal").modal("show");
        }
    });

    // Handle klik tombol Edit Item
    $(document).off("click", ".edit-items-btn").on("click", ".edit-items-btn", function () {
        const $btn = $(this);
        if ($btn.hasClass("disabled")) return;
        $btn.addClass("disabled");

        const orderNumber = $(this).data("order-number");
        const destination = $(this).data("destination");
        console.log(
            "Edit Item - Order Number:",
            orderNumber,
            "Destination:",
            destination !== undefined ? destination : "UNDEFINED"
        );
        $("#order_number").val(orderNumber);
        $("#destination").val(destination);
        $("#addItemModalLabel").text("Edit Item untuk Order Number: " + orderNumber);
        $("#itemsContainer").empty();

        $.ajax({
            url: "../API/Fetch_TaskItems.php?order_number=" + encodeURIComponent(orderNumber),
            type: "GET",
            dataType: "json",
            success: function (data) {
                console.log("Jumlah item dari server:", data.length);
                console.log("Data:", data);
                if (data.length > 0) {
                    const firstItem = data[0];
                    $('#addItemForm [name="date_pickup"]').val(firstItem.date_pickup);
                    $('#addItemForm [name="driver_name"]').val(firstItem.driver_name);
                    data.forEach((item) => {
                        const itemRow = `
                            <tr class="item-row">
                                <td>
                                    <select name="item_name[]" class="form-control select2-item" required>
                                        <option value="">Pilih Item Code</option>
                                        <option value="${item.item_name}" selected>${item.item_name}</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="number" name="qty[]" class="form-control" value="${item.qty}" required>
                                </td>
                                <td>
                                    <input type="text" name="uom[]" class="form-control uom" readonly required>
                                </td>
                                <td>
                                    <input type="text" name="dimension[]" class="form-control dimensi" readonly required>
                                </td>
                                
                                <td>
                                    <button type="button" class="btn btn-sm remove-item">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <input type="hidden" name="item_id[]" value="${item.id}">
                                </td>
                            </tr>
                        `;
                        $("#itemsContainer").append(itemRow);

                        const $newSelect = $("#itemsContainer").find(".select2-item").last();
                        initializeSelect2($newSelect, "#addItemModal");
                        $newSelect.val(item.item_name).trigger("change");
                    });
                } else {
                    addItemRow();
                }
                $("#addItemForm").attr("action", "../modules/Proses_EditItem.php");
                $("#addItemModal").modal("show");
            },
            error: function () {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "Gagal mengambil data item.",
                    confirmButtonText: "OK"
                });
            },
            complete: function () {
                $btn.removeClass("disabled");
            }
        });
    });

    // Handle submit form tambah/edit item
    $("#addItemForm").on("submit", function (e) {
        e.preventDefault();
        $.ajax({
            url: $(this).attr("action"),
            type: "POST",
            data: $(this).serialize(),
            dataType: "json",
            success: function (response) {
                Swal.fire({
                    icon: response.status,
                    title: response.status === "success" ? "Update Successfully!" : "Update Failed Please Try Again!",
                    text: response.message,
                    confirmButtonText: "OK"
                }).then(() => {
                    if (response.status === "success") {
                        $("#addItemModal").modal("hide");
                        $("#itemsContainer").empty();
                        if (itemsTable && currentOrderNumber) {
                            itemsTable.ajax
                                .url("../API/Fetch_TaskItems.php?order_number=" + encodeURIComponent(currentOrderNumber))
                                .load();
                        }
                        if (currentPage === "NewTask.php" && newTaskTable) {
                            newTaskTable.ajax.reload();
                        } else if (currentPage === "Completed.php" && completedTaskTable) {
                            completedTaskTable.ajax.reload();
                        }
                    }
                });
            },
            error: function () {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "Terjadi kesalahan pada server.",
                    confirmButtonText: "OK"
                });
            }
        });
    });

    // Handle klik tombol Lihat Item
    $(document).on("click", ".view-items-btn", function () {
        const orderNumber = $(this).data("order-number");
        currentOrderNumber = orderNumber;
        $("#viewItemsModalLabel").text("Daftar Item untuk Order Number: " + orderNumber);

        itemsTable = $("#tabelItems").DataTable({
            destroy: true,
            ajax: {
                url: "../API/Fetch_TaskItems.php?order_number=" + encodeURIComponent(orderNumber),
                dataSrc: ""
            },
            columns: [
                { data: null, render: (data, type, row, meta) => meta.row + 1 },
                { data: "item_name" },
                { data: "panjang" },
                { data: "lebar" },
                { data: "tinggi" },
                { data: "qty" },
                { data: "uom" },
                {
                    data: null,
                    render: (data, type, row) => {
                        return `
                            <button class="btn btn-sm delete-item-btn" data-id="${row.id}">
                                <i class="fas fa-trash"></i>
                            </button>
                        `;
                    },
                    orderable: false
                }
            ],
            order: [[0, "asc"]],
            language: {
                url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json",
            },
            scrollX: true,
            fixedColumns: {
                leftColumns: 0,
                rightColumns: 1,
            }
        });

        $("#viewItemsModal").modal("show");
    });

    // Handle klik tombol Hapus Item
    $(document).on("click", ".delete-item-btn", function () {
        const itemId = $(this).data("id");
        Swal.fire({
            title: "Apakah Anda yakin?",
            text: "Item ini akan dihapus secara permanen!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Ya, hapus!",
            cancelButtonText: "Batal"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "../modules/Proses_DeleteItem.php",
                    type: "POST",
                    data: { item_id: itemId },
                    dataType: "json",
                    beforeSend: function () {
                        showLoading();
                    },
                    success: function (response) {
                        hideLoading();
                        Swal.fire({
                            icon: response.status,
                            title: response.status === "success" ? "Berhasil!" : "Gagal!",
                            text: response.message,
                            confirmButtonText: "OK"
                        }).then(() => {
                            if (response.status === "success") {
                                if (itemsTable) {
                                    itemsTable.ajax.reload();
                                }
                                if (currentPage === "Completed" && completedTaskTable) {
                                    completedTaskTable.ajax.reload();
                                }
                            }
                        });
                    },
                    error: function () {
                        hideLoading();
                        Swal.fire({
                            icon: "error",
                            title: "Gagal",
                            text: "Terjadi kesalahan pada server!",
                            confirmButtonText: "OK"
                        });
                    }
                });
            }
        });
    });

    // Handle klik tombol Hapus Task
    $(document).on("click", ".delete-btn", function () {
        const taskId = $(this).data("id");
        Swal.fire({
            title: "Apakah Anda yakin?",
            text: "Task ini dan semua item terkait akan dihapus secara permanen!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Ya, hapus!",
            cancelButtonText: "Batal"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "../modules/Proses_DeleteTask.php",
                    type: "POST",
                    data: { task_id: taskId },
                    dataType: "json",
                    beforeSend: function () {
                        showLoading();
                    },
                    success: function (response) {
                        hideLoading();
                        Swal.fire({
                            icon: response.status,
                            title: response.status === "success" ? "Berhasil!" : "Gagal!",
                            text: response.message,
                            confirmButtonText: "OK"
                        }).then(() => {
                            if (response.status === "success") {
                                if (currentPage === "NewTask.php" && newTaskTable) {
                                    newTaskTable.ajax.reload();
                                } else if (currentPage === "Completed.php" && completedTaskTable) {
                                    completedTaskTable.ajax.reload();
                                } else if (currentPage === "OrderReq_Detail.php" && orderReqDetailTable) {
                                    orderReqDetailTable.ajax.reload();
                                }
                            }
                        });
                    },
                    error: function () {
                        hideLoading();
                        Swal.fire({
                            icon: "error",
                            title: "Gagal",
                            text: "Terjadi kesalahan pada server!",
                            confirmButtonText: "OK"
                        });
                    }
                });
            }
        });
    });

    // Select2 cleanup saat modal ditutup
    $("#addItemModal").on("hidden.bs.modal", function () {
        $("#itemsContainer").find(".select2-item").each(function () {
            $(this).select2("destroy");
        });
        // Reset form
        $("#addItemForm")[0].reset();
    });

    // Register Users
    $("#userForm").on("submit", function (e) {
        e.preventDefault();
        const submitBtn = $(this).find('button[type="submit"]');
        submitBtn.prop("disabled", true);
        showLoading();

        $.ajax({
            url: "../php/proses_register_user.php",
            type: "POST",
            data: $(this).serialize(),
            dataType: "json",
            success: function (response) {
                hideLoading();
                submitBtn.prop("disabled", false);
                if (response.status === "success") {
                    Swal.fire({
                        icon: "success",
                        title: "success",
                        text: response.message || "Register berhasil!",
                        showConfirmButton: true,
                        timer: 3000
                    }).then(() => {
                        $("#userModal").modal("hide");
                        $("#userForm")[0].reset();
                        $('select[name="role"]').val("").trigger("change");
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Gagal",
                        text: response.message || "Terjadi kesalahan saat Register!"
                    });
                }
            },
            error: function (xhr, status, error) {
                hideLoading();
                submitBtn.prop("disabled", false);
                let response;
                try {
                    response = JSON.parse(xhr.responseText);
                } catch (e) {
                    response = {
                        status: "error",
                        message: "Terjadi kesalahan tidak terduga!"
                    };
                }
                Swal.fire({
                    icon: "error",
                    title: "Gagal",
                    text: response.message || "Terjadi kesalahan pada server!"
                });
            }
        });
    });

    // Load data role saat modal user ditampilkan
    $("#userModal").on("shown.bs.modal", function () {
        let $role = $('select[name="role"]');
        $.ajax({
            url: "../API/get_data_role.php",
            type: "GET",
            data: {
                table: "role",
                column: "role",
                display: "role"
            },
            dataType: "json",
            success: function (response) {
                if (response.status === "success") {
                    $role.empty();
                    $role.append('<option value="">Pilih Role</option>');
                    $.each(response.data, function (index, item) {
                        $role.append(new Option(item.text, item.id));
                    });
                    $role.select2({
                        width: "100%",
                        dropdownParent: $("#userModal"),
                        allowClear: true,
                        placeholder: "Pilih Role",
                        language: {
                            noResults: function () {
                                return "Data tidak ditemukan";
                            },
                            searching: function () {
                                return "Mencari...";
                            }
                        }
                    });
                } else {
                    console.error("Gagal memuat Role:", response.message);
                }
            },
            error: function (xhr, status, error) {
                console.error("Error:", error);
            }
            
        });
        let $wh_name = $('select[name="wh_name"]');
        $.ajax({
            url: "../API/get_region.php",
            type: "GET",
            data: {
                table: "region",
                column: "region",
                display: "region"
            },
            dataType: "json",
            success: function (response) {
                if (response.status === "success") {
                    $wh_name.empty();
                    $wh_name.append('<option value="">Pilih Region</option>');
                    $.each(response.data, function (index, item) {
                        $wh_name.append(new Option(item.text, item.id));
                    });
                    $wh_name.select2({
                        width: "100%",
                        dropdownParent: $("#userModal"),
                        allowClear: true,
                        placeholder: "Pilih Region",
                        language: {
                            noResults: function () {
                                return "Data tidak ditemukan";
                            },
                            searching: function () {
                                return "Mencari...";
                            }
                        }
                    });
                } else {
                    console.error("Gagal memuat Role:", response.message);
                }
            },
            error: function (xhr, status, error) {
                console.error("Error:", error);
            }
            
        });

    });

    

    // Reset dropdown saat modal user ditutup
    $("#userModal").on("hidden.bs.modal", function () {
        $("#userForm")[0].reset();
        $('select[name="role"]').val("").trigger("change");
        $('select[name="wh_name"]').val("").trigger("change");
    });

    // Data Materials
$("#tabelMaterials").DataTable({
    ajax: {
        url: "../API/data_materials.php",
        dataSrc: ""
    },
    columns: [
        {
            data: null,
            render: function (data, type, row, meta) {
                return meta.row + meta.settings._iDisplayStart + 1;
            }
        },
        { data: "item_name" },
        { data: "dimensi" },
        { data: "uom" },
        {
            data: null,
            render: function (data, type, row) {
                return `
                    <button class="btn btn-warning btn-sm edit-material-btn" data-id="${row.id}">
                        <i class="fas fa-pen"></i> Edit
                    </button>
                `;
            },
            orderable: false
        }
    ],
    order: [[1, "asc"]],
    language: {
        url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
    }
});

// Handler untuk tombol Edit Materials
$(document).on("click", ".edit-material-btn", function () {
    const materialId = $(this).data("id");
    const table = $("#tabelMaterials").DataTable();
    const rowData = table.row($(this).closest('tr')).data();

    // Isi field modal dengan data dari baris yang dipilih
    $("#material_id").val(rowData.id);
    $("#item_name").val(rowData.item_name).prop("disabled", true); // Nonaktifkan item_name saat edit
    $("#dimensi").val(rowData.dimensi);
    $("#uom").val(rowData.uom);

    // Ubah judul modal dan teks tombol untuk mode edit
    $("#materialsModalLabel").text("Edit Data Materials");
    $("#saveMaterialsBtn").text("Perbarui Data");

    // Tampilkan modal
    $("#MaterialsModal").modal("show");
});

// Handler untuk submit form Materials
$("#MaterialsForm").on("submit", function (e) {
    e.preventDefault();

    let formData = new FormData(this);
    // Tambahkan item_name ke formData meskipun disabled, untuk konsistensi dengan backend
    formData.set("item_name", $("#item_name").val());

    $.ajax({
        url: "../modules/proses_register_materials.php",
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        dataType: "json",
        beforeSend: function () {
            $('#MaterialsForm button[type="submit"]').prop("disabled", true).html("Menyimpan...");
        },
        success: function (response) {
            if (response.status === "success") {
                Swal.fire({
                    icon: "success",
                    title: "Berhasil",
                    text: response.message
                }).then(() => {
                    $("#MaterialsModal").modal("hide");
                    $("#MaterialsForm")[0].reset();
                    $("#tabelMaterials").DataTable().ajax.reload();
                });
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Gagal",
                    text: response.message
                });
            }
        },
        error: function (xhr, status, error) {
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "Terjadi kesalahan: " + (xhr.responseText || error)
            });
        },
        complete: function () {
            $('#MaterialsForm button[type="submit"]').prop("disabled", false).html("Simpan Data");
        }
    });
});

// Reset form saat modal Materials ditutup
$("#MaterialsModal").on("hidden.bs.modal", function () {
    $("#MaterialsForm")[0].reset();
    $("#material_id").val("");
    $("#item_name").prop("disabled", false); // Aktifkan kembali item_name
    $("#materialsModalLabel").text("Tambah Data Materials");
    $("#saveMaterialsBtn").text("Simpan Data");
});

// Export Excel Materials
$("#exportExcelMaterials").on("click", function () {
    window.location.href = "../modules/export_materials";
});

    // Chart untuk Dashboard
    const dashboardTasksTable = $("#tabelDashboardTasks").DataTable({
        ajax: {
            url: "../API/Fetch_Dasboard_NewTask.php",
            dataSrc: ""
        },
        columns: [
            {
                data: null,
                render: function (data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            },
            { data: "order_number" },
            { data: "site_id" },
            { data: "site_name" },
            { data: "customer" },
            { data: "destination" },
            { data: "created_at" },
            { data: "status" }
        ],
        order: [[0, "asc"]],
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
        },
        scrollX: true
    });

    // Ambil data untuk dashboard
$.ajax({
    url: "../API/Fetch_Dasboard_NewTask.php",
    type: "GET",
    dataType: "json",
    success: function (data) {
        console.log("Data untuk dashboard:", data);

        // Hitung total tasks berdasarkan status
        const totalPendingTasks = data.filter(item => item.status === 'pending').length;
        const totalCompletedTasks = data.filter(item => item.status === 'completed').length;
        const totalAllTasks = data.length;

        // Tampilkan statistik
        $("#totalPendingTasks").text(totalPendingTasks);
        $("#totalCompletedTasks").text(totalCompletedTasks);
        $("#totalAllTasks").text(totalAllTasks);

        // Tasks per Region
        const regions = [...new Set(data.map((item) => item.wh_name))];
        const tasksPerRegion = regions.map((region) => {
            return data.filter((item) => item.wh_name === region).length;
        });

        const tasksPerRegionChart = new Chart(
            document.getElementById("tasksPerRegionChart"),
            {
                type: "bar",
                data: {
                    labels: regions,
                    datasets: [
                        {
                            label: "Jumlah Task per Region",
                            data: tasksPerRegion,
                            backgroundColor: "rgba(54, 162, 235, 0.6)",
                            borderColor: "rgba(54, 162, 235, 1)",
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: "Jumlah Task"
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: "Region (Warehouse)"
                            }
                        }
                    },
                    plugins: {
                        title: {
                            display: true,
                            text: 'Tasks per Region'
                        }
                    }
                }
            }
        );

        // Tasks per Destination
        const destinations = [...new Set(data.map((item) => item.destination))];
        const tasksPerDestination = destinations.map((destination) => {
            return data.filter((item) => item.destination === destination).length;
        });
        
        const tasksPerDestinationChart = new Chart(
            document.getElementById("tasksPerDestinationChart"),
            {
                type: "pie",
                data: {
                    labels: destinations,
                    datasets: [
                        {
                            label: "Jumlah Task",
                            data: tasksPerDestination,
                            backgroundColor: [
                                "rgba(255, 99, 132, 0.6)",
                                "rgba(54, 162, 235, 0.6)",
                                "rgba(255, 206, 86, 0.6)",
                                "rgba(75, 192, 192, 0.6)",
                                "rgba(153, 102, 255, 0.6)",
                                "rgba(255, 159, 64, 0.6)",
                                "rgba(255, 205, 86, 0.6)",
                                "rgba(75, 192, 192, 0.6)",
                                "rgba(153, 102, 255, 0.6)",
                                "rgba(255, 159, 64, 0.6)"
                            ],
                            borderColor: [
                                "rgba(255, 99, 132, 1)",
                                "rgba(54, 162, 235, 1)",
                                "rgba(255, 206, 86, 1)",
                                "rgba(75, 192, 192, 1)",
                                "rgba(153, 102, 255, 1)",
                                "rgba(255, 159, 64, 1)",
                                "rgba(255, 205, 86, 1)",
                                "rgba(75, 192, 192, 1)",
                                "rgba(153, 102, 255, 1)",
                                "rgba(255, 159, 64, 1)"
                            ],
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    plugins: {
                        legend: {
                            position: "right"
                        },
                        title: {
                            display: true,
                            text: 'Tasks per Destination'
                        }
                    }
                }
            }
        );

        // Tasks Over Time
        const dates = [...new Set(data.map((item) => item.created_at.split(" ")[0]))].sort();
        const tasksOverTime = dates.map((date) => {
            return data.filter((item) => item.created_at.split(" ")[0] === date).length;
        });
        
        const tasksOverTimeChart = new Chart(
            document.getElementById("tasksOverTimeChart"),
            {
                type: "line",
                data: {
                    labels: dates,
                    datasets: [
                        {
                            label: "Jumlah Task",
                            data: tasksOverTime,
                            fill: false,
                            borderColor: "rgba(75, 192, 192, 1)",
                            tension: 0.1
                        }
                    ]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: "Jumlah Task"
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: "Tanggal"
                            }
                        }
                    },
                    plugins: {
                        title: {
                            display: true,
                            text: 'Tasks Over Time'
                        }
                    }
                }
            }
        );
        
        console.log("Dashboard statistics:", {
            pendingTasks: totalPendingTasks,
            completedTasks: totalCompletedTasks,
            totalTasks: totalAllTasks,
            regionsCount: regions.length,
            destinationsCount: destinations.length
        });
    },
    error: function (xhr, status, error) {
        console.error("Gagal mengambil data untuk dashboard:", status, error);
        Swal.fire({
            icon: "error",
            title: "Error",
            text: "Gagal mengambil data untuk dashboard: " + error,
            confirmButtonText: "OK"
        });
    }
});

    // Logika untuk Settings.php - Inisialisasi DataTable untuk Users
    let usersTable = null;
    if (currentPage === "settings.php") {
        if (!usersTable) {
            usersTable = $("#tabelusers").DataTable({
                ajax: {
                    url: "../API/get_users.php",
                    dataSrc: "",
                },
                columns: [
                    {
                        data: null,
                        render: function (data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        },
                    },
                    { data: "nama" },
                    { data: "username" },
                    { data: "wh_name" },
                    { data: "role" },
                    {
                        data: null,
                        render: function (data, type, row) {
                            return `
                                <button class="btn btn-warning btn-sm reset-password-btn" data-id="${row.id}" data-nama="${row.nama}">
                                    <i class="fas fa-key"></i> Reset
                                </button>
                            `;
                        },
                        orderable: false,
                    },
                ],
                order: [[0, "asc"]],
                language: {
                    url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json",
                },
                scrollX: true,
                fixedColumns: {
                    leftColumns: 0,
                    rightColumns: 1,
                },
            });
        }
    }

    // Handle klik tombol Reset Password
    $(document).on("click", ".reset-password-btn", function () {
        const userId = $(this).data("id");
        const userNama = $(this).data("nama");
        $("#resetPasswordModalLabel").text("Reset Password untuk " + userNama);
        $("#resetPasswordForm [name='user_id']").val(userId);
        $("#resetPasswordModal").modal("show");
    });

    // Handle submit form Reset Password
    $("#resetPasswordForm").on("submit", function (e) {
        e.preventDefault();
        const submitBtn = $(this).find('button[type="submit"]');
        submitBtn.prop("disabled", true).text("Mereset...");
        showLoading();

        $.ajax({
            url: "../API/api_reset_password.php",
            type: "POST",
            data: $(this).serialize(),
            dataType: "json",
            success: function (response) {
                hideLoading();
                submitBtn.prop("disabled", false).text("Reset Password");
                if (response.success) {
                    Swal.fire({
                        icon: "success",
                        title: "success",
                        text: response.success || "Password berhasil direset!",
                        showConfirmButton: true,
                        timer: 3000,
                    }).then(() => {
                        $("#resetPasswordModal").modal("hide");
                        $("#resetPasswordForm")[0].reset();
                        if (usersTable) {
                            usersTable.ajax.reload();
                        }
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Gagal",
                        text: response.error || "Terjadi kesalahan saat mereset password!",
                    });
                }
            },
            error: function (xhr) {
                hideLoading();
                submitBtn.prop("disabled", false).text("Reset Password");
                let response;
                try {
                    response = JSON.parse(xhr.responseText);
                } catch (e) {
                    response = {
                        error: "Terjadi kesalahan tidak terduga!",
                    };
                }
                Swal.fire({
                    icon: "error",
                    title: "Gagal",
                    text: response.error || "Terjadi kesalahan pada server!",
                });
            }
        });
    });

    // Reset form saat modal Reset Password ditutup
    $("#resetPasswordModal").on("hidden.bs.modal", function () {
        $("#resetPasswordForm")[0].reset();
    });
    
    if (currentPage === "upload_log") {
        const fileStatusTable = $("#fileStatusTable").DataTable({
            ajax: {
                url: "../API/Fetch_QueueTasks",
                dataSrc: ""
            },
            columns: [
                {
                    data: null,
                    render: function (data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                { data: "file_name" },
                { 
                    data: "status",
                    render: function (data) {
                        if (data === 'success') {
                            return '<span class="badge bg-success">Success</span>';
                        } else if (data === 'warning') {
                            return '<span class="badge bg-warning">Warning</span>';
                        } else if (data === 'error') {
                            return '<span class="badge bg-danger">Error</span>';
                        } else {
                            return '<span class="badge bg-info">Pending</span>';
                        }
                    }
                },
                { 
                    data: "success_count",
                    render: function (data) {
                        return data || '0';
                    }
                },
                { 
                    data: "error_message",
                    render: function (data) {
                        return data ? data : '-';
                    }
                },
                { data: "created_at" },
                { data: "username" },
                {
                    data: null,
                    render: function (data, type, row) {
                        return row.report_path ? `
                            <a href="../modules/Download_ErrorReport.php?id=${row.id}" class="btn btn-sm btn-primary">
                                <i class="fas fa-file"></i>Download
                            </a>
                        ` : '-';
                    },
                    orderable: false
                }
            ],
            order: [[5, "desc"]],
            language: {
                url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
            },
            scrollX: true,
            fixedColumns: {
                leftColumns: 0,
                rightColumns: 1,
            },
        });

        $("#refreshBtn").on("click", function () {
            fileStatusTable.ajax.reload();
        });
    }
    
    if (currentPage === "OrderReq_Detail.php") {
        // Export Excel OrderReq_Detail
        $("#exportOrderReq_Detail").on("click", function () {
            window.location.href = "../modules/export_OrderReq_Detail.php";
        });
    }
    
    // Fungsi Drag And Drop Bulk Upload newTask
    // Ketika tombol Browse diklik, picu klik pada input file
    $('#browseButton').on('click', function() {
        $('#fileInput').click();
    });

    // Ketika file dipilih, tampilkan informasi file dan aktifkan tombol Unggah
    $('#fileInput').on('change', function(event) {
        const file = event.target.files[0];
        if (file) {
            // Tampilkan nama file
            $('#fileName').text(file.name);
            // Tampilkan ukuran file dalam MB
            const fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);
            $('#fileSize').text(fileSizeMB + ' MB');
            // Tampilkan bagian informasi file
            $('#fileInfo').show();
            // Aktifkan tombol Unggah
            $('#uploadButton').prop('disabled', false);
            // Simulasikan progress bar (opsional, sesuaikan dengan kebutuhan)
            $('#progressContainer').show();
            $('#progressBar').css('width', '100%').text('100%');
        } else {
            // Sembunyikan informasi file jika tidak ada file
            $('#fileInfo').hide();
            $('#uploadButton').prop('disabled', true);
            $('#progressContainer').hide();
        }
    });

    // Ketika tombol hapus file diklik, reset form
    $('#removeFile').on('click', function() {
        $('#fileInput').val(''); // Reset input file
        $('#fileName').text('');
        $('#fileSize').text('');
        $('#fileInfo').hide();
        $('#uploadButton').prop('disabled', true);
        $('#progressContainer').hide();
    });
});