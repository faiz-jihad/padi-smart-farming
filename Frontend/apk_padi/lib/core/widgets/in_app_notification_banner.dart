import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:padi/core/router/app_router.dart';
import 'package:padi/features/notifications/data/models/app_notification_model.dart';

/// Global interactive Heads-Up Notification Banner
/// Displays dynamic floating alert from the top of the screen when real-time
/// events are broadcast from Laravel Reverb.
class InAppNotificationBanner {
  InAppNotificationBanner._();

  static OverlayEntry? _currentEntry;
  static Timer? _dismissTimer;

  /// Show banner globally across whatever screen is currently open
  static void showGlobal(
    AppNotificationModel notification, {
    VoidCallback? onTap,
  }) {
    final overlayState = rootNavigatorKey.currentState?.overlay;
    if (overlayState == null) return;

    // Trigger haptic vibration for tactile alert
    try {
      HapticFeedback.heavyImpact();
    } catch (_) {}

    // Dismiss existing banner if still displayed
    dismiss();

    _currentEntry = OverlayEntry(
      builder: (context) => _InAppNotificationBannerWidget(
        notification: notification,
        onTap: () {
          dismiss();
          if (onTap != null) {
            onTap();
          } else {
            rootNavigatorKey.currentState?.pushNamed('/notifications');
          }
        },
        onDismiss: dismiss,
      ),
    );

    overlayState.insert(_currentEntry!);

    // Auto-dismiss after 6 seconds
    _dismissTimer?.cancel();
    _dismissTimer = Timer(const Duration(seconds: 6), () {
      dismiss();
    });
  }

  /// Dismiss active banner
  static void dismiss() {
    _dismissTimer?.cancel();
    _dismissTimer = null;
    _currentEntry?.remove();
    _currentEntry = null;
  }
}

class _InAppNotificationBannerWidget extends StatefulWidget {
  const _InAppNotificationBannerWidget({
    required this.notification,
    required this.onTap,
    required this.onDismiss,
  });

  final AppNotificationModel notification;
  final VoidCallback onTap;
  final VoidCallback onDismiss;

  @override
  State<_InAppNotificationBannerWidget> createState() =>
      _InAppNotificationBannerWidgetState();
}

class _InAppNotificationBannerWidgetState
    extends State<_InAppNotificationBannerWidget>
    with SingleTickerProviderStateMixin {
  late final AnimationController _animController;
  late final Animation<Offset> _offsetAnimation;
  late final Animation<double> _fadeAnimation;

  @override
  void initState() {
    super.initState();
    _animController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 380),
    );

    _offsetAnimation = Tween<Offset>(
      begin: const Offset(0.0, -1.2),
      end: Offset.zero,
    ).animate(
      CurvedAnimation(
        parent: _animController,
        curve: Curves.easeOutBack,
      ),
    );

    _fadeAnimation = CurvedAnimation(
      parent: _animController,
      curve: Curves.easeIn,
    );

    _animController.forward();
  }

  @override
  void dispose() {
    _animController.dispose();
    super.dispose();
  }

  Future<void> _handleDismiss() async {
    await _animController.reverse();
    widget.onDismiss();
  }

  @override
  Widget build(BuildContext context) {
    final topPadding = MediaQuery.of(context).padding.top;
    final item = widget.notification;

    Color accentColor;
    IconData iconData;

    switch (item.type) {
      case 'warning':
      case 'early_warning':
      case 'disease_outbreak':
        accentColor = const Color(0xFFEF4444); // Amber/Red
        iconData = Icons.warning_amber_rounded;
        break;
      case 'crop_alert':
      case 'planting_reminder':
      case 'cultivation':
        accentColor = const Color(0xFF10B981); // Emerald
        iconData = Icons.eco_rounded;
        break;
      case 'marketplace_deal':
      case 'market_offer':
      case 'marketplace':
        accentColor = const Color(0xFF3B82F6); // Blue
        iconData = Icons.storefront_rounded;
        break;
      case 'ppl_case':
      case 'ppl_result':
      case 'ppl_validation':
        accentColor = const Color(0xFFF59E0B); // Amber
        iconData = Icons.assignment_turned_in_rounded;
        break;
      default:
        accentColor = const Color(0xFF8B5CF6); // Purple/System
        iconData = Icons.notifications_active_rounded;
    }

    return Positioned(
      top: topPadding + 8,
      left: 14,
      right: 14,
      child: SlideTransition(
        position: _offsetAnimation,
        child: FadeTransition(
          opacity: _fadeAnimation,
          child: GestureDetector(
            onTap: widget.onTap,
            onVerticalDragUpdate: (details) {
              if (details.primaryDelta! < -5) {
                _handleDismiss();
              }
            },
            child: Material(
              color: Colors.transparent,
              child: Container(
                decoration: BoxDecoration(
                  color: const Color(0xFF0F172A).withValues(alpha: 0.96),
                  borderRadius: BorderRadius.circular(18),
                  border: Border.all(
                    color: accentColor.withValues(alpha: 0.4),
                    width: 1.5,
                  ),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withValues(alpha: 0.35),
                      blurRadius: 20,
                      offset: const Offset(0, 8),
                    ),
                    BoxShadow(
                      color: accentColor.withValues(alpha: 0.15),
                      blurRadius: 15,
                      offset: const Offset(0, 2),
                    ),
                  ],
                ),
                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                child: Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Icon Badge
                    Container(
                      width: 44,
                      height: 44,
                      decoration: BoxDecoration(
                        color: accentColor.withValues(alpha: 0.18),
                        shape: BoxShape.circle,
                        border: Border.all(
                          color: accentColor.withValues(alpha: 0.5),
                          width: 1.2,
                        ),
                      ),
                      child: Icon(iconData, color: accentColor, size: 24),
                    ),
                    const SizedBox(width: 12),

                    // Content
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Row(
                            children: [
                              Container(
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 7,
                                  vertical: 2,
                                ),
                                decoration: BoxDecoration(
                                  color: accentColor.withValues(alpha: 0.2),
                                  borderRadius: BorderRadius.circular(6),
                                ),
                                child: Text(
                                  item.categoryLabel,
                                  style: TextStyle(
                                    color: accentColor,
                                    fontSize: 10.5,
                                    fontWeight: FontWeight.w700,
                                  ),
                                ),
                              ),
                              const Spacer(),
                              Text(
                                'Sekarang',
                                style: TextStyle(
                                  color: Colors.white.withValues(alpha: 0.5),
                                  fontSize: 10,
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 4),
                          Text(
                            item.title,
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                            style: const TextStyle(
                              color: Colors.white,
                              fontSize: 13.5,
                              fontWeight: FontWeight.w700,
                              height: 1.2,
                            ),
                          ),
                          if (item.body.isNotEmpty) ...[
                            const SizedBox(height: 2),
                            Text(
                              item.body,
                              maxLines: 2,
                              overflow: TextOverflow.ellipsis,
                              style: TextStyle(
                                color: Colors.white.withValues(alpha: 0.8),
                                fontSize: 11.5,
                                height: 1.25,
                              ),
                            ),
                          ],
                        ],
                      ),
                    ),

                    // Close Button
                    GestureDetector(
                      onTap: _handleDismiss,
                      child: Padding(
                        padding: const EdgeInsets.only(left: 6, top: 2),
                        child: Icon(
                          Icons.close_rounded,
                          size: 18,
                          color: Colors.white.withValues(alpha: 0.5),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}
