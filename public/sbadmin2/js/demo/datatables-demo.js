// Call the dataTables jQuery plugin safely (avoid crashing on empty tables with colspan rows)
$(document).ready(function() {
  var tableEl = $('#dataTable');
  if (tableEl.length && tableEl.find('tbody td[colspan]').length === 0) {
    tableEl.DataTable({
      language: {
        search: "Cari:",
        lengthMenu: "Tampilkan _MENU_ data per halaman",
        zeroRecords: "Data tidak ditemukan",
        info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
        infoEmpty: "Menampilkan 0 data",
        infoFiltered: "(difilter dari _MAX_ total data)",
        paginate: {
          first: "Pertama",
          last: "Terakhir",
          next: "Selanjutnya",
          previous: "Sebelumnya"
        }
      }
    });
  }
});
