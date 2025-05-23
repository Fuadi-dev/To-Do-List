import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:frontend/settings.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import 'package:frontend/auth/login.dart';
import 'package:intl/intl.dart';

class HomePage extends StatefulWidget {
  const HomePage({Key? key}) : super(key: key);

  @override
  State<HomePage> createState() => _HomePageState();
}

class _HomePageState extends State<HomePage> {
  List<dynamic> _todos = [];
  List<dynamic> _categories = [];
  bool _isLoading = true;
  String _searchQuery = '';
  String? _statusFilter;
  
  // Stats
  int _totalTodos = 0;
  int _pendingTodos = 0;
  int _inProgressTodos = 0;
  int _completedTodos = 0;

  final TextEditingController _judulController = TextEditingController();
  final TextEditingController _deskripsiController = TextEditingController();
  final TextEditingController _tanggalController = TextEditingController();
  String _selectedStatus = 'belum_dikerjakan';
  List<int> _selectedCategories = [];
  DateTime _selectedDate = DateTime.now().add(const Duration(days: 1));

  @override
  void initState() {
    super.initState();
    _fetchData();
  }

  @override
  void dispose() {
    _judulController.dispose();
    _deskripsiController.dispose();
    _tanggalController.dispose();
    super.dispose();
  }

  Future<void> _fetchData() async {
    setState(() {
      _isLoading = true;
    });

    try {
      await Future.wait([
        _fetchTodos(),
        _fetchCategories(),
      ]);
      _calculateStats();
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Terjadi kesalahan: $e'),
          backgroundColor: Colors.red,
        ),
      );
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  Future<void> _fetchCategories() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final token = prefs.getString('token');

      if (token == null) {
        _logout();
        return;
      }

      final response = await http.get(
        Uri.parse('$base_url/get-category'), // Menggunakan endpoint yang benar
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $token',
        },
      );

      final responseData = jsonDecode(response.body);

      if (response.statusCode == 200) {
        setState(() {
          _categories = responseData['data'];
        });
      } else {
        // Jika gagal mendapatkan kategori, set sebagai list kosong
        setState(() {
          _categories = [];
        });
      }
    } catch (e) {
      print('Error fetching categories: $e');
      setState(() {
        _categories = [];
      });
    }
  }

  Future<void> _fetchTodos() async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('token');

    if (token == null) {
      _logout();
      return;
    }

    final response = await http.get(
      Uri.parse('$base_url/todos'),
      headers: {
        'Content-Type': 'application/json',
        'Authorization': 'Bearer $token',
      },
    );

    final responseData = jsonDecode(response.body);

    if (response.statusCode == 200) {
      setState(() {
        _todos = responseData['data'] ?? [];
      });
    } else {
      // Set todos ke array kosong jika errornya karena "tidak ada data"
      setState(() {
        _todos = [];
      });
      
      // Hanya tampilkan pesan error jika bukan karena "tidak ada data"
      final errorMessage = responseData['message'] ?? 'Gagal mengambil data';
      if (errorMessage != 'Gagal mendapatkan data') {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(errorMessage),
            backgroundColor: Colors.red,
          ),
        );
      }
    }
  }

  void _calculateStats() {
    _totalTodos = _todos.length;
    _pendingTodos = _todos.where((todo) => todo['status'] == 'belum_dikerjakan').length;
    _inProgressTodos = _todos.where((todo) => todo['status'] == 'proses').length;
    _completedTodos = _todos.where((todo) => todo['status'] == 'selesai').length;
  }

  List<dynamic> get _filteredTodos {
    return _todos.where((todo) {
      // Filter by search query
      final matchesQuery = todo['judul_tugas'].toLowerCase().contains(_searchQuery.toLowerCase()) ||
          todo['deskripsi_tugas'].toLowerCase().contains(_searchQuery.toLowerCase());
      
      // Filter by status
      final matchesStatus = _statusFilter == null || todo['status'] == _statusFilter;
      
      return matchesQuery && matchesStatus;
    }).toList();
  }

  Future<void> _logout() async {
    final prefs = await SharedPreferences.getInstance();
    
    try {
      final token = prefs.getString('token');
      if (token != null) {
        await http.post(
          Uri.parse('$base_url/logout'),
          headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer $token',
          },
        );
      }
    } catch (e) {
      // Jika endpoint logout gagal, tetap lanjut hapus token lokal
    }
    
    await prefs.remove('token');
    
    if (!mounted) return;
    Navigator.of(context).pushAndRemoveUntil(
      MaterialPageRoute(builder: (context) => const LoginPage()),
      (route) => false,
    );
  }

  Future<void> _showAddEditTodoDialog({Map<dynamic, dynamic>? todo, bool fromDetail = false}) async {
    // Jika fungsi ini dipanggil dari halaman detail, tutup modal detail terlebih dahulu
    if (fromDetail) {
      Navigator.of(context).pop(); // Tutup modal detail
    }
    
    // Reset form atau isi dengan data todo jika mengedit
    if (todo != null) {
      _judulController.text = todo['judul_tugas'];
      _deskripsiController.text = todo['deskripsi_tugas'];
      _selectedStatus = todo['status'];
      _selectedDate = DateTime.parse(todo['tanggal_selesai']);
      _tanggalController.text = DateFormat('yyyy-MM-dd').format(_selectedDate);
      _selectedCategories = (todo['categories'] as List?)
              ?.map((cat) => cat['id'] as int)
              .toList() ?? [];
    } else {
      _judulController.clear();
      _deskripsiController.clear();
      _selectedStatus = 'belum_dikerjakan';
      _selectedDate = DateTime.now().add(const Duration(days: 1));
      _tanggalController.text = DateFormat('yyyy-MM-dd').format(_selectedDate);
      _selectedCategories = [];
    }

    await showDialog(
      context: context,
      builder: (context) => StatefulBuilder(
        builder: (context, setDialogState) => AlertDialog(
          title: Text(todo == null ? 'Tambah Tugas' : 'Edit Tugas'),
          content: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                TextField(
                  controller: _judulController,
                  decoration: const InputDecoration(
                    labelText: 'Judul Tugas',
                    border: OutlineInputBorder(),
                  ),
                ),
                const SizedBox(height: 16),
                TextField(
                  controller: _deskripsiController,
                  decoration: const InputDecoration(
                    labelText: 'Deskripsi',
                    border: OutlineInputBorder(),
                  ),
                  maxLines: 3,
                ),
                const SizedBox(height: 16),
                TextField(
                  controller: _tanggalController,
                  readOnly: true,
                  decoration: const InputDecoration(
                    labelText: 'Tanggal Selesai',
                    border: OutlineInputBorder(),
                    suffixIcon: Icon(Icons.calendar_today),
                  ),
                  onTap: () async {
                    final picked = await showDatePicker(
                      context: context,
                      initialDate: _selectedDate,
                      firstDate: DateTime.now(),
                      lastDate: DateTime.now().add(const Duration(days: 365)),
                    );
                    if (picked != null) {
                      setDialogState(() {
                        _selectedDate = picked;
                        _tanggalController.text = DateFormat('yyyy-MM-dd').format(picked);
                      });
                    }
                  },
                ),
                const SizedBox(height: 16),
                DropdownButtonFormField<String>(
                  value: _selectedStatus,
                  decoration: const InputDecoration(
                    labelText: 'Status',
                    border: OutlineInputBorder(),
                  ),
                  items: const [
                    DropdownMenuItem(
                      value: 'belum_dikerjakan',
                      child: Text('Belum Dikerjakan'),
                    ),
                    DropdownMenuItem(
                      value: 'proses',
                      child: Text('Proses'),
                    ),
                    DropdownMenuItem(
                      value: 'selesai',
                      child: Text('Selesai'),
                    ),
                  ],
                  onChanged: (value) {
                    if (value != null) {
                      setDialogState(() {
                        _selectedStatus = value;
                      });
                    }
                  },
                ),
                if (_categories.isNotEmpty) ...[
                  const SizedBox(height: 16),
                  const Align(
                    alignment: Alignment.centerLeft,
                    child: Text(
                      'Kategori',
                      style: TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                  const SizedBox(height: 8),
                  Container(
                    width: double.infinity,
                    decoration: BoxDecoration(
                      border: Border.all(color: Colors.grey.shade300),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    padding: const EdgeInsets.all(12),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        if (_selectedCategories.isEmpty)
                          const Padding(
                            padding: EdgeInsets.only(bottom: 8),
                            child: Text(
                              'Belum ada kategori yang dipilih',
                              style: TextStyle(
                                color: Colors.grey,
                                fontStyle: FontStyle.italic,
                              ),
                            ),
                          ),
                        Wrap(
                          spacing: 8,
                          runSpacing: 8,
                          children: [
                            for (final category in _categories)
                              FilterChip(
                                label: Text(category['name']),
                                selected: _selectedCategories.contains(category['id']),
                                selectedColor: Colors.deepPurple.shade100,
                                checkmarkColor: Colors.deepPurple,
                                onSelected: (selected) {
                                  setDialogState(() {
                                    if (selected) {
                                      _selectedCategories.add(category['id']);
                                    } else {
                                      _selectedCategories.remove(category['id']);
                                    }
                                  });
                                },
                              ),
                          ],
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 8),
                  Align(
                    alignment: Alignment.centerRight,
                    child: Text(
                      'Pilih kategori sesuai kebutuhan (bisa lebih dari satu)',
                      style: TextStyle(
                        fontSize: 12,
                        color: Colors.grey.shade600,
                        fontStyle: FontStyle.italic,
                      ),
                    ),
                  ),
                ],
              ],
            ),
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.of(context).pop(),
              child: const Text('BATAL'),
            ),
            FilledButton(
              onPressed: () {
                Navigator.of(context).pop();
                if (todo == null) {
                  _createTodo();
                } else {
                  _updateTodo(todo['id'], originalTodo: todo);
                }
              },
              child: Text(todo == null ? 'TAMBAH' : 'SIMPAN'),
            ),
          ],
        ),
      ),
    );
  }

  Future<void> _createTodo() async {
    setState(() {
      _isLoading = true;
    });

    try {
      final prefs = await SharedPreferences.getInstance();
      final token = prefs.getString('token');

      if (token == null) {
        _logout();
        return;
      }

      final response = await http.post(
        Uri.parse('$base_url/postTodo'),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $token',
        },
        body: jsonEncode({
          'judul_tugas': _judulController.text,
          'deskripsi_tugas': _deskripsiController.text,
          'tanggal_selesai': _tanggalController.text,
          'status': _selectedStatus,
          'categories': _selectedCategories,
        }),
      );

      final responseData = jsonDecode(response.body);

      if (response.statusCode == 200) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Berhasil menambahkan tugas'),
            backgroundColor: Colors.green,
          ),
        );
        _fetchData();
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(responseData['message'] ?? 'Gagal menambahkan tugas'),
            backgroundColor: Colors.red,
          ),
        );
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Terjadi kesalahan: $e'),
          backgroundColor: Colors.red,
        ),
      );
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  Future<void> _updateTodo(int id, {Map<dynamic, dynamic>? originalTodo}) async {
    setState(() {
      _isLoading = true;
    });

    try {
      final prefs = await SharedPreferences.getInstance();
      final token = prefs.getString('token');

      if (token == null) {
        _logout();
        return;
      }

      final response = await http.put(
        Uri.parse('$base_url/todos-update/$id'),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $token',
        },
        body: jsonEncode({
          'judul_tugas': _judulController.text,
          'deskripsi_tugas': _deskripsiController.text,
          'tanggal_selesai': _tanggalController.text,
          'status': _selectedStatus,
          'categories': _selectedCategories,
        }),
      );

      final responseData = jsonDecode(response.body);

      if (response.statusCode == 200) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Berhasil mengupdate tugas'),
            backgroundColor: Colors.green,
          ),
        );
        
        // Refresh semua data
        await _fetchData();
        
        // Jika ini dipanggil dari detail (originalTodo != null)
        // tampilkan detail yang diperbarui
        if (originalTodo != null) {
          // Cari todo yang baru diperbarui
          final updatedTodo = _todos.firstWhere(
            (t) => t['id'] == id,
            orElse: () => null,
          );
          
          if (updatedTodo != null) {
            // Tampilkan detail yang sudah diperbarui tanpa duplikasi
            _showTodoDetails(updatedTodo);
          }
        }
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(responseData['message'] ?? 'Gagal mengupdate tugas'),
            backgroundColor: Colors.red,
          ),
        );
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Terjadi kesalahan: $e'),
          backgroundColor: Colors.red,
        ),
      );
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  Future<void> _deleteTodo(int id) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Konfirmasi'),
        content: const Text('Apakah Anda yakin ingin menghapus tugas ini?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(false),
            child: const Text('BATAL'),
          ),
          FilledButton(
            onPressed: () => Navigator.of(context).pop(true),
            style: FilledButton.styleFrom(
              backgroundColor: Colors.red,
            ),
            child: const Text('HAPUS'),
          ),
        ],
      ),
    );

    if (confirmed != true) return;

    setState(() {
      _isLoading = true;
    });

    try {
      final prefs = await SharedPreferences.getInstance();
      final token = prefs.getString('token');

      if (token == null) {
        _logout();
        return;
      }

      final response = await http.delete(
        Uri.parse('$base_url/todos-delete/$id'),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $token',
        },
      );

      final responseData = jsonDecode(response.body);

      if (response.statusCode == 200) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Berhasil menghapus tugas'),
            backgroundColor: Colors.green,
          ),
        );
        _fetchData();
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(responseData['message'] ?? 'Gagal menghapus tugas'),
            backgroundColor: Colors.red,
          ),
        );
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Terjadi kesalahan: $e'),
          backgroundColor: Colors.red,
        ),
      );
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: NestedScrollView(
        headerSliverBuilder: (context, innerBoxIsScrolled) {
          return [
            SliverAppBar(
              expandedHeight: 160.0, // Kurangi dari 180.0
              floating: false,
              pinned: true,
              flexibleSpace: FlexibleSpaceBar(
                centerTitle: false,
                background: Container(
                  decoration: BoxDecoration(
                    gradient: LinearGradient(
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                      colors: [
                        Colors.deepPurple.shade700,
                        Colors.deepPurple.shade300,
                      ],
                    ),
                  ),
                  child: Padding(
                    padding: const EdgeInsets.fromLTRB(8, 70, 8, 8), // Kurangi padding
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'Your Tasks',
                          style: TextStyle(
                            color: Colors.white,
                            fontSize: 24, // Kurangi ukuran font
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        const SizedBox(height: 10), // Kurangi space
                        SingleChildScrollView(
                          scrollDirection: Axis.horizontal,
                          child: Row(
                            children: [
                              _buildStatCard(
                                title: 'Total',
                                count: _totalTodos,
                                color: Colors.white,
                              ),
                              const SizedBox(width: 4),
                              _buildStatCard(
                                title: 'Pending',
                                count: _pendingTodos,
                                color: Colors.red.shade200,
                              ),
                              const SizedBox(width: 4),
                              _buildStatCard(
                                title: 'In Progress',
                                count: _inProgressTodos,
                                color: Colors.amber.shade200,
                              ),
                              const SizedBox(width: 4),
                              _buildStatCard(
                                title: 'Completed',
                                count: _completedTodos,
                                color: Colors.green.shade200,
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ),
              actions: [
                IconButton(
                  icon: const Icon(Icons.logout, color: Colors.white),
                  onPressed: _logout,
                  tooltip: 'Logout',
                ),
              ],
            ),
            SliverPersistentHeader(
              pinned: true,
              delegate: _SearchBarDelegate(
                child: Container(
                  color: Colors.white,
                  child: Padding(
                    padding: const EdgeInsets.all(8.0),
                    child: Card(
                      elevation: 4,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 8.0),
                        child: Row(
                          children: [
                            const Icon(Icons.search),
                            const SizedBox(width: 8),
                            Expanded(
                              child: TextField(
                                decoration: const InputDecoration(
                                  hintText: 'Cari tugas...',
                                  border: InputBorder.none,
                                ),
                                onChanged: (value) {
                                  setState(() {
                                    _searchQuery = value;
                                  });
                                },
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                  ),
                ),
              ),
            ),
            SliverPersistentHeader(
              pinned: true,
              delegate: _FilterBarDelegate(
                child: Container(
                  color: Colors.white,
                  padding: const EdgeInsets.symmetric(horizontal: 16.0, vertical: 8.0),
                  child: SingleChildScrollView(
                    scrollDirection: Axis.horizontal,
                    child: Row(
                      children: [
                        FilterChip(
                          label: const Text('Semua'),
                          selected: _statusFilter == null,
                          onSelected: (selected) {
                            setState(() {
                              _statusFilter = selected ? null : _statusFilter;
                            });
                          },
                        ),
                        const SizedBox(width: 8),
                        FilterChip(
                          label: const Text('Belum Dikerjakan'),
                          selected: _statusFilter == 'belum_dikerjakan',
                          onSelected: (selected) {
                            setState(() {
                              _statusFilter = selected ? 'belum_dikerjakan' : null;
                            });
                          },
                          backgroundColor: Colors.white,
                          selectedColor: Colors.red.shade100,
                        ),
                        const SizedBox(width: 8),
                        FilterChip(
                          label: const Text('Proses'),
                          selected: _statusFilter == 'proses',
                          onSelected: (selected) {
                            setState(() {
                              _statusFilter = selected ? 'proses' : null;
                            });
                          },
                          backgroundColor: Colors.white,
                          selectedColor: Colors.amber.shade100,
                        ),
                        const SizedBox(width: 8),
                        FilterChip(
                          label: const Text('Selesai'),
                          selected: _statusFilter == 'selesai',
                          onSelected: (selected) {
                            setState(() {
                              _statusFilter = selected ? 'selesai' : null;
                            });
                          },
                          backgroundColor: Colors.white,
                          selectedColor: Colors.green.shade100,
                        ),
                      ],
                    ),
                  ),
                ),
              ),
            ),
          ];
        },
        body: _isLoading 
          ? const Center(child: CircularProgressIndicator())
          : _filteredTodos.isEmpty
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(
                        Icons.task_alt,
                        size: 80,
                        color: Colors.grey.shade400,
                      ),
                      const SizedBox(height: 16),
                      Text(
                        _todos.isEmpty 
                          ? 'Belum ada tugas'
                          : 'Tidak ada tugas yang sesuai filter',
                        style: TextStyle(
                          fontSize: 18,
                          color: Colors.grey.shade600,
                        ),
                      ),
                    ],
                  ),
                )
              : ListView.builder(
                  padding: const EdgeInsets.all(16),
                  itemCount: _filteredTodos.length,
                  itemBuilder: (context, index) {
                    final todo = _filteredTodos[index];
                    return _buildTodoCard(todo);
                  },
                ),
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () => _showAddEditTodoDialog(),
        icon: const Icon(Icons.add),
        label: const Text('Tambah Tugas'),
        backgroundColor: Colors.deepPurple,
        foregroundColor: Colors.white,
      ),
    );
  }

  Widget _buildStatCard({required String title, required int count, required Color color}) {
    return Card(
      elevation: 1, // Kurangi elevasi
      color: color,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(6), // Kurangi radius
      ),
      child: Container(
        width: 80, // Tetapkan lebar tetap
        padding: const EdgeInsets.symmetric(horizontal: 4.0, vertical: 4.0), // Kurangi padding
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              title,
              style: TextStyle(
                fontSize: 9, // Kurangi font
                color: title == 'Total' ? Colors.deepPurple : Colors.black87,
                fontWeight: FontWeight.w500,
              ),
            ),
            Text(
              '$count',
              style: TextStyle(
                fontSize: 14, // Kurangi font
                fontWeight: FontWeight.bold,
                color: title == 'Total' ? Colors.deepPurple : Colors.black87,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildTodoCard(Map<dynamic, dynamic> todo) {
    final DateTime tanggalSelesai = DateTime.parse(todo['tanggal_selesai']);
    final bool isOverdue = tanggalSelesai.isBefore(DateTime.now()) && 
                          todo['status'] != 'selesai';
    
    return Card(
      margin: const EdgeInsets.only(bottom: 16),
      elevation: 3,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(16),
        side: BorderSide(
          color: isOverdue 
            ? Colors.red.withOpacity(0.5) 
            : Colors.transparent,
          width: 1.5,
        ),
      ),
      child: InkWell(
        onTap: () => _showTodoDetails(todo),
        borderRadius: BorderRadius.circular(16),
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          todo['judul_tugas'],
                          style: const TextStyle(
                            fontSize: 18,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        const SizedBox(height: 8),
                        Text(
                          todo['deskripsi_tugas'],
                          style: TextStyle(
                            fontSize: 14,
                            color: Colors.grey.shade700,
                          ),
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ],
                    ),
                  ),
                  _buildStatusChip(todo['status']),
                ],
              ),
              const SizedBox(height: 12),
              const Divider(),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Row(
                    children: [
                      Icon(
                        Icons.calendar_today,
                        size: 16,
                        color: isOverdue ? Colors.red : Colors.grey.shade600,
                      ),
                      const SizedBox(width: 4),
                      Text(
                        DateFormat('dd MMM yyyy').format(tanggalSelesai),
                        style: TextStyle(
                          fontSize: 14,
                          color: isOverdue ? Colors.red : Colors.grey.shade600,
                          fontWeight: isOverdue ? FontWeight.bold : FontWeight.normal,
                        ),
                      ),
                      if (isOverdue) ...[
                        const SizedBox(width: 4),
                        const Text(
                          'Terlambat!',
                          style: TextStyle(
                            fontSize: 12,
                            color: Colors.red,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ],
                    ],
                  ),
                  Row(
                    children: [
                      IconButton(
                        icon: const Icon(Icons.edit, size: 20, color: Colors.deepPurple),
                        onPressed: () => _showAddEditTodoDialog(todo: todo),
                        tooltip: 'Edit',
                        constraints: const BoxConstraints(),
                        padding: const EdgeInsets.all(8),
                      ),
                      IconButton(
                        icon: const Icon(Icons.delete, size: 20, color: Colors.red),
                        onPressed: () => _deleteTodo(todo['id']),
                        tooltip: 'Delete',
                        constraints: const BoxConstraints(),
                        padding: const EdgeInsets.all(8),
                      ),
                    ],
                  ),
                ],
              ),
              if ((todo['categories'] as List?)?.isNotEmpty == true) ...[
                const SizedBox(height: 8),
                Wrap(
                  spacing: 6,
                  runSpacing: 6,
                  children: [
                    for (final category in (todo['categories'] as List))
                      Chip(
                        label: Text(
                          category['name'],
                          style: const TextStyle(fontSize: 12),
                        ),
                        padding: EdgeInsets.zero,
                        materialTapTargetSize: MaterialTapTargetSize.shrinkWrap,
                        visualDensity: VisualDensity.compact,
                        backgroundColor: Colors.grey.shade200,
                      ),
                  ],
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }

  void _showTodoDetails(Map<dynamic, dynamic> todo) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (context) => DraggableScrollableSheet(
        initialChildSize: 0.7,
        minChildSize: 0.5,
        maxChildSize: 0.95,
        expand: false,
        builder: (_, controller) => SingleChildScrollView(
          controller: controller,
          padding: const EdgeInsets.all(20),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Expanded(
                    child: Text(
                      todo['judul_tugas'],
                      style: const TextStyle(
                        fontSize: 24,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                  _buildStatusChip(todo['status']),
                ],
              ),
              const SizedBox(height: 16),
              const Text(
                'Deskripsi:',
                style: TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.bold,
                ),
              ),
              const SizedBox(height: 8),
              Text(
                todo['deskripsi_tugas'],
                style: const TextStyle(fontSize: 16),
              ),
              const SizedBox(height: 24),
              Row(
                children: [
                  const Icon(Icons.calendar_today, size: 20),
                  const SizedBox(width: 8),
                  const Text(
                    'Tanggal Selesai:',
                    style: TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(width: 8),
                  Text(
                    DateFormat('dd MMMM yyyy').format(DateTime.parse(todo['tanggal_selesai'])),
                    style: const TextStyle(fontSize: 16),
                  ),
                ],
              ),
              const SizedBox(height: 12),
              Row(
                children: [
                  const Icon(Icons.access_time, size: 20),
                  const SizedBox(width: 8),
                  const Text(
                    'Dibuat pada:',
                    style: TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(width: 8),
                  Text(
                    DateFormat('dd MMM yyyy, HH:mm').format(DateTime.parse(todo['created_at'])),
                    style: const TextStyle(fontSize: 16),
                  ),
                ],
              ),
              if ((todo['categories'] as List?)?.isNotEmpty == true) ...[
                const SizedBox(height: 24),
                const Text(
                  'Kategori:',
                  style: TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                const SizedBox(height: 8),
                Wrap(
                  spacing: 8,
                  runSpacing: 8,
                  children: [
                    for (final category in (todo['categories'] as List))
                      Chip(
                        label: Text(category['name']),
                        backgroundColor: Colors.grey.shade200,
                      ),
                  ],
                ),
              ],
              const SizedBox(height: 32),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                children: [
                  ElevatedButton.icon(
                    onPressed: () => _showAddEditTodoDialog(
                      todo: todo,
                      fromDetail: true, // Tambahkan parameter ini
                    ),
                    icon: const Icon(Icons.edit),
                    label: const Text('Edit'),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.deepPurple,
                      foregroundColor: Colors.white,
                    ),
                  ),
                  ElevatedButton.icon(
                    onPressed: () {
                      // Tutup bottom sheet terlebih dahulu
                      Navigator.of(context).pop();
                      // Kemudian jalankan proses delete
                      _deleteTodo(todo['id']);
                    },
                    icon: const Icon(Icons.delete),
                    label: const Text('Hapus'),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.red,
                      foregroundColor: Colors.white,
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildStatusChip(String status) {
    Color chipColor;
    String statusText;

    switch (status) {
      case 'belum_dikerjakan':
        chipColor = Colors.red;
        statusText = 'Belum Dikerjakan';
        break;
      case 'proses':
        chipColor = Colors.amber;
        statusText = 'Proses';
        break;
      case 'selesai':
        chipColor = Colors.green;
        statusText = 'Selesai';
        break;
      default:
        chipColor = Colors.grey;
        statusText = 'Unknown';
    }

    return Chip(
      label: Text(
        statusText,
        style: const TextStyle(color: Colors.white, fontSize: 12),
      ),
      backgroundColor: chipColor,
      padding: EdgeInsets.zero,
      labelPadding: const EdgeInsets.symmetric(horizontal: 8, vertical: -2),
      visualDensity: VisualDensity.compact,
    );
  }
}

class _SearchBarDelegate extends SliverPersistentHeaderDelegate {
  final Widget child;

  _SearchBarDelegate({required this.child});

  @override
  Widget build(BuildContext context, double shrinkOffset, bool overlapsContent) {
    return child;
  }

  @override
  double get maxExtent => 60;

  @override
  double get minExtent => 60;

  @override
  bool shouldRebuild(covariant SliverPersistentHeaderDelegate oldDelegate) {
    return false;
  }
}

class _FilterBarDelegate extends SliverPersistentHeaderDelegate {
  final Widget child;

  _FilterBarDelegate({required this.child});

  @override
  Widget build(BuildContext context, double shrinkOffset, bool overlapsContent) {
    return child;
  }

  @override
  double get maxExtent => 56;

  @override
  double get minExtent => 56;

  @override
  bool shouldRebuild(covariant SliverPersistentHeaderDelegate oldDelegate) {
    return false;
  }
}