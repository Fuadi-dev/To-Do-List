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

  // Filter todos berdasarkan status seperti di website
  List<dynamic> get _activeTodos =>
      _todos.where((todo) => todo['status'] != 'selesai').toList();
  List<dynamic> get _completedTodos =>
      _todos.where((todo) => todo['status'] == 'selesai').toList();
  List<dynamic> get _overdueTodos =>
      _todos.where((todo) => todo['status'] == 'terlambat').toList();
  List<dynamic> get _inProgressTodos =>
      _todos.where((todo) => todo['status'] == 'belum_dikerjakan').toList();

  final TextEditingController _judulController = TextEditingController();
  final TextEditingController _deskripsiController = TextEditingController();
  final TextEditingController _tanggalController = TextEditingController();
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
      await Future.wait([_fetchTodos(), _fetchCategories()]);
    } catch (e) {
      _showErrorSnackBar('Terjadi kesalahan: $e');
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
        Uri.parse('$base_url/categories'),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $token',
        },
      );

      final responseData = jsonDecode(response.body);

      if (response.statusCode == 200 && responseData['status'] == true) {
        setState(() {
          _categories = responseData['data'];
        });
      } else {
        setState(() {
          _categories = [];
        });
      }
    } catch (e) {
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
      Uri.parse('$base_url/'),
      headers: {
        'Content-Type': 'application/json',
        'Authorization': 'Bearer $token',
      },
    );

    final responseData = jsonDecode(response.body);

    if (response.statusCode == 200 && responseData['status'] == true) {
      final todosData = responseData['data']['todos']['data'] ?? [];
      setState(() {
        _todos = todosData;
      });
    } else {
      setState(() {
        _todos = [];
      });
    }
  }

  List<dynamic> get _filteredTodos {
    return _todos.where((todo) {
      final matchesQuery =
          todo['judul_tugas'].toLowerCase().contains(
            _searchQuery.toLowerCase(),
          ) ||
          todo['deskripsi_tugas'].toLowerCase().contains(
            _searchQuery.toLowerCase(),
          );
      return matchesQuery;
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
      // Continue with local logout even if API fails
    }

    await prefs.remove('token');
    await prefs.remove('user_data');

    if (!mounted) return;
    Navigator.of(context).pushAndRemoveUntil(
      MaterialPageRoute(builder: (context) => const LoginPage()),
      (route) => false,
    );
  }

  Future<void> _showAddEditTodoDialog({Map<dynamic, dynamic>? todo}) async {
    if (todo != null) {
      _judulController.text = todo['judul_tugas'];
      _deskripsiController.text = todo['deskripsi_tugas'];
      _selectedDate = DateTime.parse(todo['tanggal_selesai']);
      _tanggalController.text = DateFormat('yyyy-MM-dd').format(_selectedDate);
      _selectedCategories =
          (todo['categories'] as List?)
              ?.map((cat) => cat['id'] as int)
              .toList() ??
          [];
    } else {
      _judulController.clear();
      _deskripsiController.clear();
      _selectedDate = DateTime.now().add(const Duration(days: 1));
      _tanggalController.text = DateFormat('yyyy-MM-dd').format(_selectedDate);
      _selectedCategories = [];
    }

    await showDialog(
      context: context,
      builder:
          (context) => StatefulBuilder(
            builder:
                (context, setDialogState) => AlertDialog(
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(20),
                  ),
                  title: Text(
                    todo == null ? 'Tambah Tugas' : 'Edit Tugas',
                    style: TextStyle(
                      color: Colors.deepPurple.shade700,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  content: SingleChildScrollView(
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        TextField(
                          controller: _judulController,
                          decoration: InputDecoration(
                            labelText: 'Judul Tugas',
                            labelStyle: TextStyle(
                              color: Colors.deepPurple.shade700,
                            ),
                            border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(12),
                            ),
                            focusedBorder: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(12),
                              borderSide: BorderSide(
                                color: Colors.deepPurple.shade700,
                                width: 2,
                              ),
                            ),
                          ),
                        ),
                        const SizedBox(height: 16),
                        TextField(
                          controller: _deskripsiController,
                          decoration: InputDecoration(
                            labelText: 'Deskripsi',
                            labelStyle: TextStyle(
                              color: Colors.deepPurple.shade700,
                            ),
                            border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(12),
                            ),
                            focusedBorder: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(12),
                              borderSide: BorderSide(
                                color: Colors.deepPurple.shade700,
                                width: 2,
                              ),
                            ),
                          ),
                          maxLines: 3,
                        ),
                        const SizedBox(height: 16),
                        TextField(
                          controller: _tanggalController,
                          readOnly: true,
                          decoration: InputDecoration(
                            labelText: 'Tanggal Selesai',
                            labelStyle: TextStyle(
                              color: Colors.deepPurple.shade700,
                            ),
                            border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(12),
                            ),
                            focusedBorder: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(12),
                              borderSide: BorderSide(
                                color: Colors.deepPurple.shade700,
                                width: 2,
                              ),
                            ),
                            suffixIcon: Icon(
                              Icons.calendar_today,
                              color: Colors.deepPurple.shade700,
                            ),
                          ),
                          onTap: () async {
                            final picked = await showDatePicker(
                              context: context,
                              initialDate: _selectedDate,
                              firstDate: DateTime.now(),
                              lastDate: DateTime.now().add(
                                const Duration(days: 365),
                              ),
                              builder: (context, child) {
                                return Theme(
                                  data: Theme.of(context).copyWith(
                                    colorScheme: ColorScheme.light(
                                      primary: Colors.deepPurple.shade700,
                                      onPrimary: Colors.white,
                                      surface: Colors.white,
                                      onSurface: Colors.black,
                                    ),
                                  ),
                                  child: child!,
                                );
                              },
                            );
                            if (picked != null) {
                              setDialogState(() {
                                _selectedDate = picked;
                                _tanggalController.text = DateFormat(
                                  'yyyy-MM-dd',
                                ).format(picked);
                              });
                            }
                          },
                        ),
                        if (_categories.isNotEmpty) ...[
                          const SizedBox(height: 16),
                          Align(
                            alignment: Alignment.centerLeft,
                            child: Text(
                              'Kategori',
                              style: TextStyle(
                                fontSize: 16,
                                fontWeight: FontWeight.bold,
                                color: Colors.deepPurple.shade700,
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
                            child: Wrap(
                              spacing: 8,
                              runSpacing: 8,
                              children: [
                                for (final category in _categories)
                                  FilterChip(
                                    label: Text(category['name']),
                                    selected: _selectedCategories.contains(
                                      category['id'],
                                    ),
                                    selectedColor: Colors.deepPurple.shade100,
                                    checkmarkColor: Colors.deepPurple.shade700,
                                    onSelected: (selected) {
                                      setDialogState(() {
                                        if (selected) {
                                          _selectedCategories.add(
                                            category['id'],
                                          );
                                        } else {
                                          _selectedCategories.remove(
                                            category['id'],
                                          );
                                        }
                                      });
                                    },
                                  ),
                              ],
                            ),
                          ),
                        ],
                      ],
                    ),
                  ),
                  actions: [
                    TextButton(
                      onPressed: () => Navigator.of(context).pop(),
                      child: Text(
                        'BATAL',
                        style: TextStyle(color: Colors.grey.shade600),
                      ),
                    ),
                    ElevatedButton(
                      onPressed: () {
                        Navigator.of(context).pop();
                        if (todo == null) {
                          _createTodo();
                        } else {
                          _updateTodo(todo['id']);
                        }
                      },
                      style: ElevatedButton.styleFrom(
                        backgroundColor: Colors.deepPurple.shade700,
                        foregroundColor: Colors.white,
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                      ),
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
        Uri.parse('$base_url/todo/add'),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $token',
        },
        body: jsonEncode({
          'judul_tugas': _judulController.text,
          'deskripsi_tugas': _deskripsiController.text,
          'tanggal_selesai': _tanggalController.text,
          'categories': _selectedCategories,
        }),
      );

      final responseData = jsonDecode(response.body);

      if (response.statusCode == 200 && responseData['status'] == true) {
        _showSuccessSnackBar('Berhasil menambahkan tugas');
        _fetchData();
      } else {
        _showErrorSnackBar(
          responseData['message'] ?? 'Gagal menambahkan tugas',
        );
      }
    } catch (e) {
      _showErrorSnackBar('Terjadi kesalahan: $e');
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  Future<void> _updateTodo(int id) async {
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
        Uri.parse('$base_url/todo/$id/edit'),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $token',
        },
        body: jsonEncode({
          'judul_tugas': _judulController.text,
          'deskripsi_tugas': _deskripsiController.text,
          'tanggal_selesai': _tanggalController.text,
          'categories': _selectedCategories,
        }),
      );

      final responseData = jsonDecode(response.body);

      if (response.statusCode == 200 && responseData['status'] == true) {
        _showSuccessSnackBar('Berhasil mengupdate tugas');
        _fetchData();
      } else {
        _showErrorSnackBar(responseData['message'] ?? 'Gagal mengupdate tugas');
      }
    } catch (e) {
      _showErrorSnackBar('Terjadi kesalahan: $e');
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  Future<void> _deleteTodo(int id) async {
    final confirmed = await _showConfirmDialog(
      'Konfirmasi',
      'Apakah Anda yakin ingin menghapus tugas ini?',
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
        Uri.parse('$base_url/todo/$id/delete'),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $token',
        },
      );

      final responseData = jsonDecode(response.body);

      if (response.statusCode == 200 && responseData['status'] == true) {
        _showSuccessSnackBar('Berhasil menghapus tugas');
        _fetchData();
      } else {
        _showErrorSnackBar(responseData['message'] ?? 'Gagal menghapus tugas');
      }
    } catch (e) {
      _showErrorSnackBar('Terjadi kesalahan: $e');
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  Future<void> _toggleTodoStatus(int id) async {
    final confirmed = await _showConfirmDialog(
      'Konfirmasi Penyelesaian Tugas',
      'Apakah Anda yakin ingin menandai tugas ini sebagai selesai?',
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

      final response = await http.patch(
        Uri.parse('$base_url/todo/$id/toggle-status'),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $token',
        },
      );

      final responseData = jsonDecode(response.body);

      if (response.statusCode == 200 && responseData['status'] == true) {
        _showSuccessSnackBar('Tugas berhasil diselesaikan!');
        _fetchData();
      } else {
        _showErrorSnackBar(
          responseData['message'] ?? 'Gagal mengubah status tugas',
        );
      }
    } catch (e) {
      _showErrorSnackBar('Terjadi kesalahan: $e');
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  void _showSuccessSnackBar(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: Colors.green,
        behavior: SnackBarBehavior.floating,
      ),
    );
  }

  void _showErrorSnackBar(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: Colors.red,
        behavior: SnackBarBehavior.floating,
      ),
    );
  }

  Future<bool?> _showConfirmDialog(String title, String content) {
    return showDialog<bool>(
      context: context,
      builder:
          (context) => AlertDialog(
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(20),
            ),
            title: Text(
              title,
              style: TextStyle(
                color: Colors.deepPurple.shade700,
                fontWeight: FontWeight.bold,
              ),
            ),
            content: Text(content),
            actions: [
              TextButton(
                onPressed: () => Navigator.of(context).pop(false),
                child: Text(
                  'BATAL',
                  style: TextStyle(color: Colors.grey.shade600),
                ),
              ),
              ElevatedButton(
                onPressed: () => Navigator.of(context).pop(true),
                style: ElevatedButton.styleFrom(
                  backgroundColor: Colors.deepPurple.shade700,
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                ),
                child: const Text('YA'),
              ),
            ],
          ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return DefaultTabController(
      length: 3,
      child: Scaffold(
        appBar: AppBar(
          title: const Text(
            'TaskMaster',
            style: TextStyle(fontWeight: FontWeight.bold),
          ),
          backgroundColor: Colors.deepPurple.shade700,
          foregroundColor: Colors.white,
          elevation: 0,
          actions: [
            IconButton(
              icon: const Icon(Icons.logout),
              onPressed: _logout,
              tooltip: 'Logout',
            ),
          ],
          bottom: const TabBar(
            labelColor: Colors.white,
            unselectedLabelColor: Colors.white70,
            indicatorColor: Colors.white,
            tabs: [
              Tab(icon: Icon(Icons.pending_actions), text: 'Aktif'),
              Tab(icon: Icon(Icons.warning), text: 'Terlambat'),
              Tab(icon: Icon(Icons.check_circle), text: 'Selesai'),
            ],
          ),
        ),
        body: Column(
          children: [
            // Search Bar
            Container(
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  begin: Alignment.topCenter,
                  end: Alignment.bottomCenter,
                  colors: [
                    Colors.deepPurple.shade700,
                    Colors.deepPurple.shade50,
                  ],
                ),
              ),
              padding: const EdgeInsets.all(16),
              child: TextField(
                decoration: InputDecoration(
                  hintText: 'Cari tugas...',
                  prefixIcon: Icon(
                    Icons.search,
                    color: Colors.deepPurple.shade700,
                  ),
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                    borderSide: BorderSide.none,
                  ),
                  filled: true,
                  fillColor: Colors.white,
                  focusedBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                    borderSide: BorderSide(
                      color: Colors.deepPurple.shade700,
                      width: 2,
                    ),
                  ),
                ),
                onChanged: (value) {
                  setState(() {
                    _searchQuery = value;
                  });
                },
              ),
            ),
            // Statistics Cards
            Container(
              color: Colors.deepPurple.shade50,
              padding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
              child: Row(
                children: [
                  Expanded(
                    child: _buildStatCard(
                      'Total',
                      _todos.length,
                      Colors.deepPurple.shade700,
                    ),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: _buildStatCard(
                      'Aktif',
                      _inProgressTodos.length,
                      Colors.orange,
                    ),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: _buildStatCard(
                      'Telat',
                      _overdueTodos.length,
                      Colors.red,
                    ),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: _buildStatCard(
                      'Selesai',
                      _completedTodos.length,
                      Colors.green,
                    ),
                  ),
                ],
              ),
            ),
            // Tab Views
            Expanded(
              child:
                  _isLoading
                      ? Center(
                        child: CircularProgressIndicator(
                          color: Colors.deepPurple.shade700,
                        ),
                      )
                      : TabBarView(
                        children: [
                          _buildTodoList(
                            _inProgressTodos,
                            'Belum ada tugas aktif',
                          ),
                          _buildTodoList(
                            _overdueTodos,
                            'Tidak ada tugas yang terlambat',
                          ),
                          _buildTodoList(
                            _completedTodos,
                            'Belum ada tugas yang selesai',
                          ),
                        ],
                      ),
            ),
          ],
        ),
        floatingActionButton: FloatingActionButton.extended(
          onPressed: () => _showAddEditTodoDialog(),
          icon: const Icon(Icons.add),
          label: const Text('Tambah Tugas'),
          backgroundColor: Colors.deepPurple.shade700,
          foregroundColor: Colors.white,
        ),
      ),
    );
  }

  Widget _buildStatCard(String title, int count, Color color) {
    return Card(
      elevation: 2,
      child: Container(
        padding: const EdgeInsets.all(12),
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(8),
          color: color.withOpacity(0.1),
        ),
        child: Column(
          children: [
            Text(
              '$count',
              style: TextStyle(
                fontSize: 24,
                fontWeight: FontWeight.bold,
                color: color,
              ),
            ),
            Text(
              title,
              style: TextStyle(
                fontSize: 12,
                color: color,
                fontWeight: FontWeight.w500,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildTodoList(List<dynamic> todos, String emptyMessage) {
    final filteredTodos =
        todos.where((todo) {
          return todo['judul_tugas'].toLowerCase().contains(
                _searchQuery.toLowerCase(),
              ) ||
              todo['deskripsi_tugas'].toLowerCase().contains(
                _searchQuery.toLowerCase(),
              );
        }).toList();

    if (filteredTodos.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.task_alt, size: 80, color: Colors.grey.shade400),
            const SizedBox(height: 16),
            Text(
              emptyMessage,
              style: TextStyle(fontSize: 18, color: Colors.grey.shade600),
            ),
          ],
        ),
      );
    }

    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: filteredTodos.length,
      itemBuilder: (context, index) {
        final todo = filteredTodos[index];
        return _buildTodoCard(todo);
      },
    );
  }

  Widget _buildTodoCard(Map<dynamic, dynamic> todo) {
    final DateTime tanggalSelesai = DateTime.parse(todo['tanggal_selesai']);
    final String status = todo['status'];

    Color borderColor = Colors.transparent;
    Color statusColor = Colors.grey;
    String statusText = '';

    switch (status) {
      case 'belum_dikerjakan':
        borderColor = Colors.orange;
        statusColor = Colors.orange;
        statusText = 'Belum Dikerjakan';
        break;
      case 'terlambat':
        borderColor = Colors.red;
        statusColor = Colors.red;
        statusText = 'Terlambat';
        break;
      case 'selesai':
        borderColor = Colors.green;
        statusColor = Colors.green;
        statusText = 'Selesai';
        break;
    }

    return Card(
      margin: const EdgeInsets.only(bottom: 16),
      elevation: 3,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(12),
        side: BorderSide(color: borderColor.withOpacity(0.5), width: 1),
      ),
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
                        style: TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                          color: Colors.deepPurple.shade700,
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
                Chip(
                  label: Text(
                    statusText,
                    style: const TextStyle(color: Colors.white, fontSize: 12),
                  ),
                  backgroundColor: statusColor,
                  materialTapTargetSize: MaterialTapTargetSize.shrinkWrap,
                ),
              ],
            ),
            const SizedBox(height: 12),
            Row(
              children: [
                Icon(Icons.calendar_today, size: 16, color: statusColor),
                const SizedBox(width: 4),
                Text(
                  DateFormat('dd MMM yyyy').format(tanggalSelesai),
                  style: TextStyle(
                    fontSize: 14,
                    color: statusColor,
                    fontWeight: FontWeight.w500,
                  ),
                ),
                if (status == 'terlambat') ...[
                  const SizedBox(width: 8),
                  Text(
                    '(${DateTime.now().difference(tanggalSelesai).inDays} hari)',
                    style: const TextStyle(
                      fontSize: 12,
                      color: Colors.red,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ],
              ],
            ),
            if ((todo['categories'] as List?)?.isNotEmpty == true) ...[
              const SizedBox(height: 12),
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
                      materialTapTargetSize: MaterialTapTargetSize.shrinkWrap,
                      visualDensity: VisualDensity.compact,
                      backgroundColor: Colors.deepPurple.shade100,
                    ),
                ],
              ),
            ],
            const SizedBox(height: 12),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                if (status != 'selesai')
                  Row(
                    children: [
                      Checkbox(
                        value: false,
                        onChanged: (value) {
                          if (value == true) {
                            _toggleTodoStatus(todo['id']);
                          }
                        },
                        activeColor: Colors.green,
                      ),
                      const Text(
                        'Tandai Selesai',
                        style: TextStyle(fontSize: 14),
                      ),
                    ],
                  )
                else
                  Row(
                    children: [
                      Checkbox(
                        value: true,
                        onChanged: null,
                        activeColor: Colors.green,
                      ),
                      const Text(
                        'Selesai',
                        style: TextStyle(fontSize: 14, color: Colors.grey),
                      ),
                    ],
                  ),
                Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    if (status != 'selesai')
                      IconButton(
                        icon: Icon(
                          Icons.edit,
                          size: 20,
                          color: Colors.deepPurple.shade700,
                        ),
                        onPressed: () => _showAddEditTodoDialog(todo: todo),
                        tooltip: 'Edit',
                      ),
                    IconButton(
                      icon: const Icon(
                        Icons.delete,
                        size: 20,
                        color: Colors.red,
                      ),
                      onPressed: () => _deleteTodo(todo['id']),
                      tooltip: 'Delete',
                    ),
                  ],
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}
