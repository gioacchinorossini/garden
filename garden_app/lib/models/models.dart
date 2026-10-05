class User {
  final int id;
  final String name;
  final String email;
  final String role; // 'admin', 'landowner', 'gardener'
  final String phone;
  final String status;

  User({
    required this.id,
    required this.name,
    required this.email,
    required this.role,
    this.phone = '',
    this.status = 'active',
  });

  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: int.tryParse(json['id']?.toString() ?? '0') ?? 0,
      name: json['name']?.toString() ?? 'Unknown User',
      email: json['email']?.toString() ?? '',
      role: json['role']?.toString().toLowerCase() ?? 'gardener',
      phone: json['phone']?.toString() ?? '',
      status: json['status']?.toString() ?? 'active',
    );
  }

  Map<String, dynamic> toJson() => {
    'id': id,
    'name': name,
    'email': email,
    'role': role,
    'phone': phone,
    'status': status,
  };

  bool get isAdmin => role == 'admin';
  bool get isLandowner => role == 'landowner';
  bool get isGardener => role == 'gardener';
}

class Land {
  final int id;
  final String title;
  final String address;
  final double latitude;
  final double longitude;
  final double area;
  final String status; // 'approved', 'pending', 'rejected'
  final String reason;
  final String description;
  final String crops;
  final int totalPlots;
  final int occupiedPlots;
  final bool has3dView;
  final String createdAt;
  List<Plot> plots;

  Land({
    required this.id,
    required this.title,
    required this.address,
    required this.latitude,
    required this.longitude,
    required this.area,
    this.status = 'approved',
    this.reason = '',
    this.description = '',
    this.crops = '',
    this.totalPlots = 0,
    this.occupiedPlots = 0,
    this.has3dView = false,
    this.createdAt = '',
    List<Plot>? plots,
  }) : plots = plots ?? [];

  factory Land.fromJson(Map<String, dynamic> json) {
    return Land(
      id: int.tryParse(json['id']?.toString() ?? '0') ?? 0,
      title: json['title']?.toString() ?? 'Untitled Land',
      address: json['address']?.toString() ?? '',
      latitude: double.tryParse(json['latitude']?.toString() ?? '14.5995') ?? 14.5995,
      longitude: double.tryParse(json['longitude']?.toString() ?? '120.9842') ?? 120.9842,
      area: double.tryParse(json['area']?.toString() ?? '0') ?? 0.0,
      status: json['status']?.toString() ?? 'approved',
      reason: json['reason']?.toString() ?? '',
      description: json['description']?.toString() ?? '',
      crops: json['crops']?.toString() ?? (json['allowed_seeds']?.toString() ?? ''),
      totalPlots: int.tryParse(json['total_plots']?.toString() ?? '0') ?? 0,
      occupiedPlots: int.tryParse(json['occupied_plots']?.toString() ?? '0') ?? 0,
      has3dView: (json['has_3d_view'] == 1 || json['has_3d_view'] == true || json['has_3d_view'] == '1'),
      createdAt: json['created_at']?.toString() ?? '',
    );
  }

  int get availablePlots => (totalPlots > occupiedPlots) ? (totalPlots - occupiedPlots) : 0;
}

class Plot {
  final int id;
  final int landId;
  final String plotNumber;
  final double area;
  final String status; // 'available', 'occupied', 'maintenance'
  final String crop;
  final String cropIcon;
  final String crops;
  final String farmerName;

  Plot({
    required this.id,
    required this.landId,
    required this.plotNumber,
    required this.area,
    this.status = 'available',
    this.crop = '',
    this.cropIcon = '',
    this.crops = '',
    this.farmerName = '',
  });

  factory Plot.fromJson(Map<String, dynamic> json) {
    return Plot(
      id: int.tryParse(json['id']?.toString() ?? '0') ?? 0,
      landId: int.tryParse(json['land_id']?.toString() ?? '0') ?? 0,
      plotNumber: json['plot_number']?.toString() ?? 'Plot',
      area: double.tryParse(json['area']?.toString() ?? '0') ?? 0.0,
      status: json['status']?.toString() ?? 'available',
      crop: json['crop']?.toString() ?? '',
      cropIcon: json['crop_icon']?.toString() ?? '',
      crops: json['crops']?.toString() ?? '',
      farmerName: json['farmer_name']?.toString() ?? '',
    );
  }

  bool get isAvailable => status.toLowerCase() == 'available';
  bool get isOccupied => status.toLowerCase() == 'occupied';
}

class RequestModel {
  final int id;
  final int gardenerId;
  final String gardenerName;
  final int landId;
  final int plotId;
  final String landTitle;
  final String plotNumber;
  final String purpose;
  final String duration;
  final String status; // 'pending', 'approved', 'rejected'
  final String notes;
  final String requestedAt;

  RequestModel({
    required this.id,
    this.gardenerId = 0,
    required this.gardenerName,
    required this.landId,
    required this.plotId,
    required this.landTitle,
    required this.plotNumber,
    required this.purpose,
    required this.duration,
    required this.status,
    this.notes = '',
    this.requestedAt = '',
  });

  factory RequestModel.fromJson(Map<String, dynamic> json) {
    return RequestModel(
      id: int.tryParse(json['id']?.toString() ?? '0') ?? 0,
      gardenerId: int.tryParse(json['gardener_id']?.toString() ?? '0') ?? 0,
      gardenerName: json['gardener']?.toString() ?? (json['gardener_name']?.toString() ?? 'Community Gardener'),
      landId: int.tryParse(json['land_id']?.toString() ?? '0') ?? 0,
      plotId: int.tryParse(json['plot_id']?.toString() ?? '0') ?? 0,
      landTitle: json['land_title']?.toString() ?? 'Land Property',
      plotNumber: json['plot_num']?.toString() ?? (json['plot_number']?.toString() ?? 'Plot'),
      purpose: json['purpose']?.toString() ?? (json['message']?.toString() ?? ''),
      duration: json['duration']?.toString() ?? (json['requested_duration']?.toString() ?? '6 months'),
      status: json['status']?.toString().toLowerCase() ?? 'pending',
      notes: json['notes']?.toString() ?? (json['response_notes']?.toString() ?? ''),
      requestedAt: json['requested_at']?.toString() ?? '',
    );
  }

  bool get isPending => status == 'pending';
  bool get isApproved => status == 'approved';
  bool get isRejected => status == 'rejected';
}

class ScheduleItem {
  final int id;
  final String landTitle;
  final String? gardener;
  final String title;
  final String description;
  final String startTime;
  final String endTime;
  final String taskType; // 'watering', 'planting', 'weeding', 'harvesting', 'meeting', 'other'
  final String status; // 'pending', 'in_progress', 'completed'
  final String difficulty;
  final int xp;
  final String? proofImage;
  final String? completedBy;
  final String? completedAt;
  final String? completionNotes;

  ScheduleItem({
    required this.id,
    required this.landTitle,
    this.gardener,
    required this.title,
    required this.description,
    required this.startTime,
    required this.endTime,
    required this.taskType,
    required this.status,
    this.difficulty = 'medium',
    this.xp = 100,
    this.proofImage,
    this.completedBy,
    this.completedAt,
    this.completionNotes,
  });

  factory ScheduleItem.fromJson(Map<String, dynamic> json) {
    return ScheduleItem(
      id: int.tryParse(json['id']?.toString() ?? '0') ?? 0,
      landTitle: json['land_title']?.toString() ?? 'Community Garden',
      gardener: json['gardener']?.toString(),
      title: json['title']?.toString() ?? 'Garden Task',
      description: json['description']?.toString() ?? '',
      startTime: json['start_time']?.toString() ?? '',
      endTime: json['end_time']?.toString() ?? '',
      taskType: json['task_type']?.toString().toLowerCase() ?? 'other',
      status: json['status']?.toString().toLowerCase() ?? 'pending',
      difficulty: json['difficulty']?.toString() ?? 'medium',
      xp: int.tryParse(json['xp']?.toString() ?? '100') ?? 100,
      proofImage: json['proof_image']?.toString(),
      completedBy: json['completed_by']?.toString(),
      completedAt: json['completed_at']?.toString(),
      completionNotes: json['completion_notes']?.toString(),
    );
  }

  bool get isCompleted => status == 'completed';
}

class HarvestItem {
  final int id;
  final String cropName;
  final double quantity;
  final String unit;
  final String harvestDate;
  final String plotNum;
  final String notes;

  HarvestItem({
    required this.id,
    required this.cropName,
    required this.quantity,
    this.unit = 'kg',
    required this.harvestDate,
    required this.plotNum,
    this.notes = '',
  });

  factory HarvestItem.fromJson(Map<String, dynamic> json) {
    return HarvestItem(
      id: int.tryParse(json['id']?.toString() ?? '0') ?? 0,
      cropName: json['crop_name']?.toString() ?? 'Harvest',
      quantity: double.tryParse(json['quantity']?.toString() ?? '0') ?? 0.0,
      unit: json['unit']?.toString() ?? 'kg',
      harvestDate: json['harvest_date']?.toString() ?? '',
      plotNum: json['plot_num']?.toString() ?? '',
      notes: json['notes']?.toString() ?? '',
    );
  }
}
