import 'package:flutter/material.dart';

class PadiTextField extends StatelessWidget {
  const PadiTextField({
    super.key,
    required this.controller,
    required this.label,
    this.keyboardType,
    this.textInputAction,
    this.obscureText = false,
    this.errorText,
    this.prefixIcon,
    this.suffixIcon,
    this.decoration,
  });

  final TextEditingController controller;
  final String label;
  final TextInputType? keyboardType;
  final TextInputAction? textInputAction;
  final bool obscureText;
  final String? errorText;
  final IconData? prefixIcon;
  final Widget? suffixIcon;
  final InputDecoration? decoration;

  @override
  Widget build(BuildContext context) {
    final defaultDecoration = InputDecoration(
      labelText: label,
      prefixIcon: prefixIcon == null ? null : Icon(prefixIcon),
      suffixIcon: suffixIcon,
    );

    final effectiveDecoration = (decoration ?? defaultDecoration).copyWith(
      labelText: decoration?.labelText ?? label,
      errorText: errorText,
      prefixIcon: decoration?.prefixIcon ??
          (decoration == null && prefixIcon != null ? Icon(prefixIcon) : null),
      suffixIcon: decoration?.suffixIcon ?? (decoration == null ? suffixIcon : null),
    );

    return TextField(
      controller: controller,
      keyboardType: keyboardType,
      textInputAction: textInputAction,
      obscureText: obscureText,
      decoration: effectiveDecoration,
    );
  }
}

class ErrorBanner extends StatelessWidget {
  const ErrorBanner({
    super.key,
    required this.message,
    this.isSuccess = false,
  });

  final String message;
  final bool isSuccess;

  @override
  Widget build(BuildContext context) {
    if (message.trim().isEmpty) {
      return const SizedBox.shrink();
    }

    final backgroundColor = isSuccess
        ? const Color(0xFFE8F8F1)
        : const Color(0xFFFFEEE8);

    final borderColor = isSuccess
        ? const Color(0xFF8ED9B8)
        : const Color(0xFFFFC9B8);

    final textColor = isSuccess
        ? const Color(0xFF087443)
        : const Color(0xFF9E3B1F);

    final icon = isSuccess
        ? Icons.check_circle_outline_rounded
        : Icons.error_outline_rounded;

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.symmetric(
        horizontal: 16,
        vertical: 14,
      ),
      decoration: BoxDecoration(
        color: backgroundColor,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(
          color: borderColor,
        ),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(
            icon,
            color: textColor,
            size: 22,
          ),
          const SizedBox(width: 10),
          Expanded(
            child: Text(
              message,
              style: TextStyle(
                color: textColor,
                fontSize: 15,
                fontWeight: FontWeight.w700,
                height: 1.4,
              ),
            ),
          ),
        ],
      ),
    );
  }
}
