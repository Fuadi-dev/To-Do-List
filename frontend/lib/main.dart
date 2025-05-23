import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:frontend/auth/login.dart';
import 'package:frontend/home/home_page.dart';
import 'package:frontend/settings.dart';
import 'package:http/http.dart' as http;

void main() {
  WidgetsFlutterBinding.ensureInitialized();
  runApp(const MyApp());
}

class MyApp extends StatelessWidget {
  const MyApp({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'To-Do List App',
      theme: ThemeData(
        colorScheme: ColorScheme.fromSeed(seedColor: Colors.deepPurple),
        useMaterial3: true,
      ),
      home: const AuthCheckPage(),
    );
  }
}

class AuthCheckPage extends StatefulWidget {
  const AuthCheckPage({Key? key}) : super(key: key);

  @override
  State<AuthCheckPage> createState() => _AuthCheckPageState();
}

class _AuthCheckPageState extends State<AuthCheckPage> {
  bool _isLoading = true;
  bool _isLoggedIn = false;

  @override
  void initState() {
    super.initState();
    _checkIfLoggedIn();
  }

  Future<void> _checkIfLoggedIn() async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('token');
    
    if (token != null) {
      // Validasi token dengan mencoba mengakses API yang terproteksi
      try {
        final response = await http.get(
          Uri.parse('$base_url/user'),
          headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer $token',
          },
        );
        
        if (response.statusCode == 200) {
          setState(() {
            _isLoggedIn = true;
            _isLoading = false;
          });
          return;
        }
      } catch (e) {
        // Token tidak valid atau terjadi error jaringan
        await prefs.remove('token'); // Hapus token yang tidak valid
      }
    }
    
    // Jika sampai di sini, berarti token tidak ada atau tidak valid
    setState(() {
      _isLoggedIn = false;
      _isLoading = false;
    });
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return const Scaffold(
        body: Center(
          child: CircularProgressIndicator(),
        ),
      );
    }

    if (_isLoggedIn) {
      return const HomePage();
    } else {
      return const LoginPage();
    }
  }
}