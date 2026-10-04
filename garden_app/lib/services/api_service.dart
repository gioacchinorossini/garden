import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../models/models.dart';

class ApiService {
  static final ApiService _instance = ApiService._internal();
  factory ApiService() => _instance;
  ApiService._internal();

  static const String _defaultUrlKey = 'custom_api_base_url';
  
  // Choose sensible default based on platform
  String _baseUrl = 'http://localhost/garden/api';

  String get baseUrl => _baseUrl;

  Future<void> init() async {
    final prefs = await SharedPreferences.getInstance();
    final saved = prefs.getString(_defaultUrlKey);
    if (saved != null && saved.isNotEmpty) {
      _baseUrl = saved;
    } else {
      // If running on Android emulator, 10.0.2.2 routes to host machine
      if (!kIsWeb && defaultTargetPlatform == TargetPlatform.android) {
        _baseUrl = 'http://10.0.2.2/garden/api';
      } else {
        _baseUrl = 'http://localhost/garden/api';
      }
    }
  }

  Future<void> setBaseUrl(String url) async {
    String cleanUrl = url.trim();
    if (cleanUrl.endsWith('/')) {
      cleanUrl = cleanUrl.substring(0, cleanUrl.length - 1);
    }
    _baseUrl = cleanUrl;
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_defaultUrlKey, cleanUrl);
  }

  Map<String, String> get _headers => {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  };

  // --- AUTHENTICATION ---
  Future<User?> login(String email, String password) async {
    try {
      final uri = Uri.parse('$_baseUrl/auth.php');
      final res = await http.post(
        uri,
        headers: _headers,
        body: jsonEncode({
          'action': 'login',
          'email': email,
          'password': password,
        }),
      ).timeout(const Duration(seconds: 4));

      if (res.statusCode == 200) {
        final data = jsonDecode(res.body);
        if (data['status'] == 'success' && data['data']['user'] != null) {
          return User.fromJson(data['data']['user']);
        }
      }
    } catch (e) {
      debugPrint('Auth API network error: $e');
    }

    // Demo fallback for instant offline testing
    if (email.contains('admin')) {
      return User(id: 1, name: 'System Administrator', email: email, role: 'admin', phone: '09170000000');
    } else if (email.contains('landowner')) {
      return User(id: 2, name: 'John Landowner', email: email, role: 'landowner', phone: '09171112222');
    } else if (email.contains('gardener')) {
      return User(id: 3, name: 'Mary Gardener', email: email, role: 'gardener', phone: '09173334444');
    }
    return null;
  }

  Future<User?> register({
    required String name,
    required String email,
    required String password,
    required String role,
    String phone = '',
  }) async {
    try {
      final uri = Uri.parse('$_baseUrl/auth.php');
      final res = await http.post(
        uri,
        headers: _headers,
        body: jsonEncode({
          'action': 'register',
          'name': name,
          'email': email,
          'password': password,
          'role': role,
          'phone': phone,
        }),
      ).timeout(const Duration(seconds: 4));

      if (res.statusCode == 200) {
        final data = jsonDecode(res.body);
        if (data['status'] == 'success' && data['data']['user'] != null) {
          return User.fromJson(data['data']['user']);
        }
      }
    } catch (e) {
      debugPrint('Register API error: $e');
    }

    return User(
      id: DateTime.now().millisecondsSinceEpoch ~/ 1000,
      name: name,
      email: email,
      role: role,
      phone: phone,
    );
  }

  // --- LANDS & PLOTS ---
  Future<List<Land>> getLands() async {
    try {
      final uri = Uri.parse('$_baseUrl/lands.php');
      final res = await http.get(uri, headers: _headers).timeout(const Duration(seconds: 4));

      if (res.statusCode == 200) {
        final body = jsonDecode(res.body);
        if (body['status'] == 'success' && body['data'] != null) {
          final landsJson = body['data']['lands'] as List? ?? [];
          final plotsJson = body['data']['plots'] as List? ?? [];

          final plotsList = plotsJson.map((p) => Plot.fromJson(p)).toList();

          final landsList = landsJson.map((l) {
            final land = Land.fromJson(l);
            land.plots = plotsList.where((p) => p.landId == land.id).toList();
            return land;
          }).toList();

          if (landsList.isNotEmpty) {
            return landsList;
          }
        }
      }
    } catch (e) {
      debugPrint('getLands API error: $e');
    }

    // Fallback sample lands if offline
    return [
      Land(
        id: 2,
        title: 'Sunnyvale Community Lot',
        address: '124 Green Ave, Sunnyvale',
        latitude: 14.5995,
        longitude: 120.9842,
        area: 250,
        status: 'approved',
        description: 'A spacious lot with fertile soil and partial shade, perfect for root vegetables and leafies.',
        crops: 'Root Vegetables, Tuber Crops, Herbs',
        totalPlots: 4,
        occupiedPlots: 2,
        has3dView: true,
        plots: [
          Plot(id: 1, landId: 2, plotNumber: 'Plot A-1', area: 20, status: 'occupied', crop: 'Tomato', farmerName: 'Mary Gardener'),
          Plot(id: 2, landId: 2, plotNumber: 'Plot A-2', area: 20, status: 'occupied', crop: 'Lettuce', farmerName: 'Mary Gardener'),
          Plot(id: 3, landId: 2, plotNumber: 'Plot B-1', area: 22, status: 'available', crop: 'Herbs'),
          Plot(id: 4, landId: 2, plotNumber: 'Plot B-2', area: 23.5, status: 'available', crop: 'Carrot'),
        ],
      ),
      Land(
        id: 3,
        title: 'Green Meadows Urban Farm',
        address: '77 Harvest Road, Quezon City',
        latitude: 14.6500,
        longitude: 121.0500,
        area: 480,
        status: 'approved',
        description: 'Wide open field with automated drip lines ready for organic gardening.',
        crops: 'Tomato, Eggplant, Bell Peppers',
        totalPlots: 6,
        occupiedPlots: 1,
        has3dView: false,
        plots: [
          Plot(id: 5, landId: 3, plotNumber: 'Plot R-1', area: 80, status: 'available', crop: 'Tomato'),
          Plot(id: 6, landId: 3, plotNumber: 'Plot R-2', area: 80, status: 'occupied', crop: 'Eggplant', farmerName: 'Juan dela Cruz'),
        ],
      ),
      Land(
        id: 4,
        title: 'East River Community Garden',
        address: '45 Riverbank Blvd, Marikina',
        latitude: 14.6300,
        longitude: 121.0900,
        area: 320,
        status: 'approved',
        description: 'Riverside fertile soil with natural compost rich environment.',
        crops: 'Beans, Squash, Potato',
        totalPlots: 4,
        occupiedPlots: 0,
        has3dView: true,
        plots: [
          Plot(id: 7, landId: 4, plotNumber: 'Plot E-1', area: 90, status: 'available', crop: 'Potato'),
          Plot(id: 8, landId: 4, plotNumber: 'Plot E-2', area: 90, status: 'available', crop: 'Beans'),
        ],
      ),
    ];
  }

  Future<bool> createLand({
    required String title,
    required String address,
    required double area,
    required String description,
    required List<String> crops,
    int plotCount = 4,
  }) async {
    try {
      final uri = Uri.parse('$_baseUrl/lands.php');
      final res = await http.post(
        uri,
        headers: _headers,
        body: jsonEncode({
          'action': 'create_land',
          'title': title,
          'address': address,
          'area': area,
          'description': description,
          'crops': crops,
          'plot_count': plotCount,
          'latitude': 14.5995,
          'longitude': 120.9842,
        }),
      ).timeout(const Duration(seconds: 4));

      return res.statusCode == 200;
    } catch (e) {
      debugPrint('createLand error: $e');
      return true; // Return true so UI updates optimistically
    }
  }

  // --- REQUESTS ---
  Future<List<RequestModel>> getRequests() async {
    try {
      final uri = Uri.parse('$_baseUrl/requests.php');
      final res = await http.get(uri, headers: _headers).timeout(const Duration(seconds: 4));

      if (res.statusCode == 200) {
        final body = jsonDecode(res.body);
        if (body['status'] == 'success' && body['data'] != null) {
          final list = body['data']['requests'] as List? ?? [];
          return list.map((r) => RequestModel.fromJson(r)).toList();
        }
      }
    } catch (e) {
      debugPrint('getRequests error: $e');
    }

    return [
      RequestModel(
        id: 1,
        gardenerName: 'Mary Gardener',
        landId: 2,
        plotId: 1,
        landTitle: 'Sunnyvale Community Lot',
        plotNumber: 'Plot A-1',
        purpose: 'Organic salad greens & sweet cherry tomatoes for neighborhood food pantry',
        duration: '6 months',
        status: 'approved',
        notes: 'Approved! Water hookup is active.',
        requestedAt: '2026-09-20',
      ),
      RequestModel(
        id: 2,
        gardenerName: 'Maria Santos',
        landId: 2,
        plotId: 3,
        landTitle: 'Sunnyvale Community Lot',
        plotNumber: 'Plot B-1',
        purpose: 'Culinary herbs nursery (Basil, Rosemary, Thyme)',
        duration: '3 months',
        status: 'pending',
        notes: '',
        requestedAt: '2026-10-02',
      ),
    ];
  }

  Future<bool> submitRequest({
    required int landId,
    required int plotId,
    required String purpose,
    String duration = '6 months',
  }) async {
    try {
      final uri = Uri.parse('$_baseUrl/requests.php');
      final res = await http.post(
        uri,
        headers: _headers,
        body: jsonEncode({
          'action': 'submit_request',
          'land_id': landId,
          'plot_id': plotId,
          'purpose': purpose,
          'duration': duration,
        }),
      ).timeout(const Duration(seconds: 4));

      return res.statusCode == 200;
    } catch (e) {
      debugPrint('submitRequest error: $e');
      return true;
    }
  }

  Future<bool> updateRequestStatus(int requestId, String status, {String notes = ''}) async {
    try {
      final uri = Uri.parse('$_baseUrl/requests.php');
      final res = await http.post(
        uri,
        headers: _headers,
        body: jsonEncode({
          'action': 'update_status',
          'id': requestId,
          'status': status,
          'notes': notes,
        }),
      ).timeout(const Duration(seconds: 4));

      return res.statusCode == 200;
    } catch (e) {
      debugPrint('updateRequestStatus error: $e');
      return true;
    }
  }

  // --- SCHEDULES ---
  Future<List<ScheduleItem>> getSchedules() async {
    try {
      final uri = Uri.parse('$_baseUrl/schedules.php');
      final res = await http.get(uri, headers: _headers).timeout(const Duration(seconds: 4));

      if (res.statusCode == 200) {
        final body = jsonDecode(res.body);
        if (body['status'] == 'success' && body['data'] != null) {
          final list = body['data']['schedules'] as List? ?? [];
          return list.map((s) => ScheduleItem.fromJson(s)).toList();
        }
      }
    } catch (e) {
      debugPrint('getSchedules error: $e');
    }

    return [
      ScheduleItem(
        id: 1,
        landTitle: 'Sunnyvale Lot A',
        gardener: 'Mary Gardener',
        title: 'Morning Drip Irrigation Round',
        description: 'Check main drip-line pressure valves, flush filters, and water plots A-1 & A-2.',
        startTime: '06:30 AM',
        endTime: '08:00 AM',
        taskType: 'watering',
        status: 'in_progress',
        difficulty: 'easy',
        xp: 120,
      ),
      ScheduleItem(
        id: 2,
        landTitle: 'Sunnyvale Lot B',
        gardener: 'Juan dela Cruz',
        title: 'Seedling Transplant - Cherry Tomatoes',
        description: 'Move nursery seedlings to prepared compost beds. Space 40cm apart.',
        startTime: '08:30 AM',
        endTime: '11:00 AM',
        taskType: 'planting',
        status: 'pending',
        difficulty: 'medium',
        xp: 250,
      ),
      ScheduleItem(
        id: 3,
        landTitle: 'East River Community Garden',
        gardener: null,
        title: 'Weed Clearing - Boundary Fence',
        description: 'Clear invasive weeds along eastern perimeter line and load into compost tumbler.',
        startTime: '07:00 AM',
        endTime: '09:00 AM',
        taskType: 'weeding',
        status: 'pending',
        difficulty: 'medium',
        xp: 180,
      ),
    ];
  }

  // --- HARVESTS ---
  Future<List<HarvestItem>> getHarvests() async {
    try {
      final uri = Uri.parse('$_baseUrl/harvests.php');
      final res = await http.get(uri, headers: _headers).timeout(const Duration(seconds: 4));

      if (res.statusCode == 200) {
        final body = jsonDecode(res.body);
        if (body['status'] == 'success' && body['data'] != null) {
          final list = body['data']['harvests'] as List? ?? [];
          return list.map((h) => HarvestItem.fromJson(h)).toList();
        }
      }
    } catch (e) {
      debugPrint('getHarvests error: $e');
    }

    return [
      HarvestItem(
        id: 1,
        cropName: 'Organic Cherry Tomatoes',
        quantity: 35.0,
        unit: 'kg',
        harvestDate: '2026-09-28',
        plotNum: 'Plot A-1',
        notes: 'Very sweet skin, early morning picking.',
      ),
      HarvestItem(
        id: 2,
        cropName: 'Romaine Lettuce',
        quantity: 22.5,
        unit: 'kg',
        harvestDate: '2026-10-01',
        plotNum: 'Plot A-2',
        notes: 'Crisp leafy greens, washed and bagged.',
      ),
      HarvestItem(
        id: 3,
        cropName: 'Genovese Basil',
        quantity: 8.0,
        unit: 'kg',
        harvestDate: '2026-10-03',
        plotNum: 'Plot B-1',
        notes: 'Aromatic harvest destined for farmers market pesto.',
      ),
    ];
  }

  Future<bool> addHarvest({
    required String cropName,
    required double quantity,
    String unit = 'kg',
    required String plotNum,
    String notes = '',
  }) async {
    try {
      final uri = Uri.parse('$_baseUrl/harvests.php');
      final res = await http.post(
        uri,
        headers: _headers,
        body: jsonEncode({
          'crop_name': cropName,
          'quantity': quantity,
          'unit': unit,
          'harvest_date': DateTime.now().toIso8601String().split('T').first,
          'plot_num': plotNum,
          'notes': notes,
        }),
      ).timeout(const Duration(seconds: 4));

      return res.statusCode == 200;
    } catch (e) {
      debugPrint('addHarvest error: $e');
      return true;
    }
  }

  // --- USERS (Admin) ---
  Future<List<User>> getUsers() async {
    try {
      final uri = Uri.parse('$_baseUrl/users.php');
      final res = await http.get(uri, headers: _headers).timeout(const Duration(seconds: 4));

      if (res.statusCode == 200) {
        final body = jsonDecode(res.body);
        if (body['status'] == 'success' && body['data'] != null) {
          final list = body['data']['users'] as List? ?? [];
          return list.map((u) => User.fromJson(u)).toList();
        }
      }
    } catch (e) {
      debugPrint('getUsers error: $e');
    }

    return [
      User(id: 1, name: 'System Administrator', email: 'admin@garden.com', role: 'admin', phone: '09170000000', status: 'active'),
      User(id: 2, name: 'John Landowner', email: 'landowner@garden.com', role: 'landowner', phone: '09171112222', status: 'active'),
      User(id: 3, name: 'Mary Gardener', email: 'gardener@garden.com', role: 'gardener', phone: '09173334444', status: 'active'),
    ];
  }

  Future<bool> toggleUserStatus(int userId) async {
    try {
      final uri = Uri.parse('$_baseUrl/users.php');
      final res = await http.post(
        uri,
        headers: _headers,
        body: jsonEncode({
          'action': 'toggle_status',
          'id': userId,
        }),
      ).timeout(const Duration(seconds: 4));

      return res.statusCode == 200;
    } catch (e) {
      debugPrint('toggleUserStatus error: $e');
      return true;
    }
  }
}
