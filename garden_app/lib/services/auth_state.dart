import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../models/models.dart';
import 'api_service.dart';

class AuthState extends ChangeNotifier {
  static final AuthState _instance = AuthState._internal();
  factory AuthState() => _instance;
  AuthState._internal();

  User? _currentUser;
  bool _isLoading = false;

  User? get currentUser => _currentUser;
  bool get isAuthenticated => _currentUser != null;
  bool get isLoading => _isLoading;

  static const String _userStorageKey = 'garden_auth_user';

  Future<void> init() async {
    _isLoading = true;
    notifyListeners();
    try {
      final prefs = await SharedPreferences.getInstance();
      final userJsonStr = prefs.getString(_userStorageKey);
      if (userJsonStr != null) {
        _currentUser = User.fromJson(jsonDecode(userJsonStr));
      } else {
        // Default to demo gardener for instant preview
        _currentUser = User(
          id: 3,
          name: 'Mary Gardener',
          email: 'gardener@garden.com',
          role: 'gardener',
          phone: '09173334444',
        );
      }
    } catch (e) {
      debugPrint('AuthState init error: $e');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<bool> login(String email, String password) async {
    _isLoading = true;
    notifyListeners();
    try {
      final user = await ApiService().login(email, password);
      if (user != null) {
        _currentUser = user;
        final prefs = await SharedPreferences.getInstance();
        await prefs.setString(_userStorageKey, jsonEncode(user.toJson()));
        notifyListeners();
        return true;
      }
    } catch (e) {
      debugPrint('Login error: $e');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
    return false;
  }

  Future<bool> register({
    required String name,
    required String email,
    required String password,
    required String role,
    String phone = '',
  }) async {
    _isLoading = true;
    notifyListeners();
    try {
      final user = await ApiService().register(
        name: name,
        email: email,
        password: password,
        role: role,
        phone: phone,
      );
      if (user != null) {
        _currentUser = user;
        final prefs = await SharedPreferences.getInstance();
        await prefs.setString(_userStorageKey, jsonEncode(user.toJson()));
        notifyListeners();
        return true;
      }
    } catch (e) {
      debugPrint('Register error: $e');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
    return false;
  }

  Future<void> switchDemoRole(String role) async {
    if (role == 'landowner') {
      _currentUser = User(
        id: 2,
        name: 'John Landowner',
        email: 'landowner@garden.com',
        role: 'landowner',
        phone: '09171112222',
      );
    } else {
      _currentUser = User(
        id: 3,
        name: 'Mary Gardener',
        email: 'gardener@garden.com',
        role: 'gardener',
        phone: '09173334444',
      );
    }
    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString(_userStorageKey, jsonEncode(_currentUser!.toJson()));
    } catch (e) {
      debugPrint('Error saving role: $e');
    }
    notifyListeners();
  }

  Future<void> switchRole(String role) => switchDemoRole(role);

  Future<void> logout() async {
    _currentUser = null;
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_userStorageKey);
    notifyListeners();
  }
}
