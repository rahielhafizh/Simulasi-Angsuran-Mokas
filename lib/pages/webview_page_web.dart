import 'package:flutter/material.dart';
import 'package:flutter/foundation.dart';
import 'dart:html' as html;
import 'dart:ui_web' as ui_web;
import '../config/app_config.dart';

class WebViewPage extends StatefulWidget {
  const WebViewPage({super.key});

  @override
  State<WebViewPage> createState() => WebViewPageState();
}

class WebViewPageState extends State<WebViewPage> {
  bool isLoading = true;
  bool hasError = false;
  String errorMessage = '';

  static const String iframeViewType = 'app-iframe-view';
  static bool viewRegistered = false;

  @override
  void initState() {
    super.initState();
    AppConfig.printConfig();

    if (!viewRegistered) {
      registerIframeView();
      viewRegistered = true;
    }

    Future.delayed(const Duration(seconds: 1), () {
      if (mounted) {
        setState(() {
          isLoading = false;
        });
      }
    });
  }

  void registerIframeView() {
    ui_web.platformViewRegistry.registerViewFactory(
      iframeViewType,
      (int viewId) {
        final iframe = html.IFrameElement()
          ..src = AppConfig.baseUrl
          ..style.border = 'none'
          ..style.width = '100%'
          ..style.height = '100%'
          ..allow = 'geolocation; microphone; camera; autoplay';

        iframe.onLoad.listen((event) {
          if (kDebugMode) {
            print('IFrame loaded successfully: ${AppConfig.baseUrl}');
          }
        });

        iframe.onError.listen((event) {
          if (kDebugMode) {
            print('IFrame error loading: ${AppConfig.baseUrl}');
          }
        });

        return iframe;
      },
    );
  }

  void reloadPage() {
    html.window.location.reload();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: SafeArea(
        child: Stack(
          children: [
            const HtmlElementView(viewType: iframeViewType),
            if (isLoading) buildLoadingIndicator(),
            if (hasError) buildErrorScreen(),
            buildControlButtons(),
          ],
        ),
      ),
    );
  }

  Widget buildLoadingIndicator() {
    return Container(
      color: Colors.white,
      child: const Center(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            CircularProgressIndicator(),
            SizedBox(height: 16),
            Text(
              'Loading...',
              style: TextStyle(
                fontSize: 16,
                color: Colors.black54,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget buildErrorScreen() {
    return Container(
      color: Colors.white,
      child: Center(
        child: Padding(
          padding: const EdgeInsets.all(24.0),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Icon(
                Icons.error_outline,
                color: Colors.red,
                size: 64,
              ),
              const SizedBox(height: 16),
              const Text(
                'Failed to Load Page',
                style: TextStyle(
                  fontSize: 20,
                  fontWeight: FontWeight.bold,
                ),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 8),
              Text(
                errorMessage.isNotEmpty
                    ? errorMessage
                    : 'Make sure the server is running',
                style: const TextStyle(fontSize: 14),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 8),
              Text(
                'URL: ${AppConfig.baseUrl}',
                style: const TextStyle(
                  fontSize: 12,
                  color: Colors.black54,
                ),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 24),
              ElevatedButton.icon(
                onPressed: reloadPage,
                icon: const Icon(Icons.refresh),
                label: const Text('Retry'),
                style: ElevatedButton.styleFrom(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 32,
                    vertical: 16,
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget buildControlButtons() {
    return Positioned(
      top: 8,
      right: 8,
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          buildActionButton(
            icon: Icons.refresh,
            onPressed: reloadPage,
            tooltip: 'Reload',
          ),
        ],
      ),
    );
  }

  Widget buildActionButton({
    required IconData icon,
    required VoidCallback onPressed,
    required String tooltip,
  }) {
    return Material(
      elevation: 4,
      shape: const CircleBorder(),
      child: Tooltip(
        message: tooltip,
        child: InkWell(
          onTap: onPressed,
          customBorder: const CircleBorder(),
          child: Container(
            padding: const EdgeInsets.all(8),
            decoration: const BoxDecoration(
              color: Colors.white,
              shape: BoxShape.circle,
            ),
            child: Icon(
              icon,
              color: Colors.black87,
              size: 20,
            ),
          ),
        ),
      ),
    );
  }
}
