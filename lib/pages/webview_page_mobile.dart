import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter/foundation.dart';
import 'package:webview_flutter/webview_flutter.dart';
import '../config/app_config.dart';

class WebViewPage extends StatefulWidget {
  const WebViewPage({super.key});

  @override
  State<WebViewPage> createState() => WebViewPageState();
}

class WebViewPageState extends State<WebViewPage> {
  late final WebViewController controller;
  bool isLoading = true;
  bool hasError = false;
  String errorMessage = '';
  Timer? timeoutTimer;

  @override
  void initState() {
    super.initState();
    AppConfig.printConfig();
    initializeWebView();
  }

  void initializeWebView() {
    try {
      controller = WebViewController()
        ..setJavaScriptMode(JavaScriptMode.unrestricted)
        ..setBackgroundColor(Colors.white)
        ..setNavigationDelegate(
          NavigationDelegate(
            onPageStarted: handlePageStarted,
            onPageFinished: handlePageFinished,
            onWebResourceError: handleWebResourceError,
            onNavigationRequest: handleNavigationRequest,
          ),
        )
        ..loadRequest(Uri.parse(AppConfig.baseUrl));
    } catch (e) {
      handleInitError(e);
    }
  }

  NavigationDecision handleNavigationRequest(NavigationRequest request) {
    if (kDebugMode) {
      print('Navigation to: ${request.url}');
    }
    return NavigationDecision.navigate;
  }

  void handlePageStarted(String url) {
    if (!mounted) return;

    setState(() {
      isLoading = true;
      hasError = false;
      errorMessage = '';
    });

    startLoadingTimeout();
  }

  void handlePageFinished(String url) {
    if (!mounted) return;

    cancelLoadingTimeout();

    setState(() {
      isLoading = false;
      hasError = false;
    });

    if (kDebugMode) {
      print('Page loaded successfully: $url');
    }
  }

  void handleWebResourceError(WebResourceError error) {
    if (!mounted) return;

    cancelLoadingTimeout();

    final errorMsg = error.description;

    setState(() {
      hasError = true;
      isLoading = false;
      errorMessage = errorMsg;
    });

    if (kDebugMode) {
      print('WebView Error: $errorMsg');
      print('Error Code: ${error.errorCode}');
      print('Error Type: ${error.errorType}');
    }

    showErrorSnackBar(errorMsg);
  }

  void handleInitError(Object error) {
    if (!mounted) return;

    setState(() {
      hasError = true;
      isLoading = false;
      errorMessage = 'Failed to initialize: $error';
    });

    if (kDebugMode) {
      print('Initialization Error: $error');
    }
  }

  void startLoadingTimeout() {
    cancelLoadingTimeout();

    timeoutTimer = Timer(
      const Duration(seconds: AppConfig.connectionTimeout),
      () {
        if (mounted && isLoading) {
          setState(() {
            hasError = true;
            isLoading = false;
            errorMessage = 'Connection timeout. Please check your connection.';
          });

          if (kDebugMode) {
            print('Page load timeout after ${AppConfig.connectionTimeout}s');
          }
        }
      },
    );
  }

  void cancelLoadingTimeout() {
    timeoutTimer?.cancel();
    timeoutTimer = null;
  }

  void reloadPage() {
    if (!mounted) return;

    setState(() {
      hasError = false;
      isLoading = true;
      errorMessage = '';
    });

    controller.reload();

    if (kDebugMode) {
      print('Reloading page...');
    }
  }

  void showErrorSnackBar(String message) {
    if (!mounted) return;

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        duration: const Duration(seconds: 3),
        action: SnackBarAction(
          label: 'Retry',
          onPressed: reloadPage,
        ),
      ),
    );
  }

  Future<bool> handleBackPress() async {
    if (await controller.canGoBack()) {
      await controller.goBack();
      return false;
    }

    if (!mounted) return true;

    final shouldExit = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Exit App'),
        content: const Text('Do you want to exit the application?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(false),
            child: const Text('Cancel'),
          ),
          TextButton(
            onPressed: () => Navigator.of(context).pop(true),
            child: const Text('Exit'),
          ),
        ],
      ),
    );

    return shouldExit ?? false;
  }

  @override
  void dispose() {
    cancelLoadingTimeout();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return PopScope(
      canPop: false,
      onPopInvokedWithResult: (didPop, result) async {
        if (!didPop) {
          final shouldPop = await handleBackPress();
          if (shouldPop && mounted) {
            Navigator.of(context).pop();
          }
        }
      },
      child: Scaffold(
        body: SafeArea(
          child: Stack(
            children: [
              WebViewWidget(controller: controller),
              if (isLoading) buildLoadingIndicator(),
              if (hasError) buildErrorScreen(),
              buildControlButtons(),
            ],
          ),
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
                    : 'Please check your internet connection',
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
          const SizedBox(width: 8),
          buildActionButton(
            icon: Icons.home,
            onPressed: () =>
                controller.loadRequest(Uri.parse(AppConfig.baseUrl)),
            tooltip: 'Home',
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
