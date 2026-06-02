import 'dart:io';
import 'package:flutter/foundation.dart';

class AppConfig {
  static const String productionUrl = 'https://access.sfi.co.id/simulation_app/login.php';
  // static const String productionUrl = ' https://uat.sfi.co.id/simulation_app/login.php';

  static const String developmentUrl = 'http://localhost:8000';
  static const bool isProduction = true;

  static String get baseUrl {
    if (isProduction) {
      return productionUrl;
    }

    if (kIsWeb) {
      return developmentUrl;
    } else if (Platform.isAndroid) {
      return developmentUrl.replaceAll('localhost', '10.0.2.2');
    } else if (Platform.isIOS) {
      return developmentUrl;
    } else if (Platform.isWindows) {
      return developmentUrl;
    }

    return developmentUrl;
  }

  static const String appName = 'Simulasi Mobil Bekas';
  static const String appVersion = '1.0.1';
  static const int connectionTimeout = 30;
  static const bool enableLogging = !isProduction;

  static void printConfig() {
    if (kDebugMode) {
      print('App Name : $appName');
      print('Version : $appVersion');
      print('Mode : ${isProduction ? "PRODUCTION" : "DEVELOPMENT"}');
      print('Base URL : $baseUrl');
      print('Platform : ${getPlatformName()}');
    }
  }

  static String getPlatformName() {
    if (kIsWeb) return 'Web';
    if (Platform.isAndroid) return 'Android';
    if (Platform.isIOS) return 'iOS';
    if (Platform.isWindows) return 'Windows';
    if (Platform.isMacOS) return 'macOS';
    if (Platform.isLinux) return 'Linux';
    return 'Unknown';
  }
}
