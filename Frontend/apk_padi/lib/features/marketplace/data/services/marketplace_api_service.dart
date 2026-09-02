import 'dart:convert';

import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import 'package:image_picker/image_picker.dart';

import 'package:padi/core/network/api_client.dart';
import 'package:padi/features/marketplace/data/models/market_listing_model.dart';
import 'package:padi/features/marketplace/data/models/market_offer_model.dart';
import 'package:padi/features/marketplace/data/models/purchase_contract_model.dart';

class MarketplaceApiService {
  const MarketplaceApiService(this._apiClient);

  final ApiClient _apiClient;

  // ============================================================
  // HELPER - DECODE RESPONSE
  // ============================================================

  dynamic _decodeIfString(dynamic value) {
    if (value is! String) {
      return value;
    }

    final raw = value.trim();

    if (raw.isEmpty) {
      return value;
    }

    try {
      return jsonDecode(raw);
    } catch (_) {
      return value;
    }
  }

  // ============================================================
  // HELPER - MAP
  // ============================================================

  Map<String, dynamic>? _asMap(dynamic value) {
    value = _decodeIfString(value);

    if (value is Map) {
      return Map<String, dynamic>.from(value);
    }

    return null;
  }

  // ============================================================
  // HELPER - ERROR MESSAGE
  // ============================================================

  String _errorMessage(dynamic responseData) {
    responseData = _decodeIfString(responseData);

    if (responseData is Map) {
      final map = Map<String, dynamic>.from(responseData);

      final message = map['message'];

      if (message != null &&
          message.toString().trim().isNotEmpty) {
        return message.toString();
      }

      final errors = map['errors'];

      if (errors is Map) {
        final messages = <String>[];

        for (final value in errors.values) {
          if (value is List) {
            for (final item in value) {
              messages.add(item.toString());
            }
          } else {
            messages.add(value.toString());
          }
        }

        if (messages.isNotEmpty) {
          return messages.join('\n');
        }
      }
    }

    if (responseData is String) {
      final text = responseData.trim();

      if (text.isNotEmpty) {
        final lower = text.toLowerCase();

        if (lower.contains('<!doctype html') ||
            lower.contains('<html') ||
            lower.contains('<body')) {
          return 'Server mengembalikan halaman HTML, bukan JSON. '
              'Periksa autentikasi dan endpoint API.';
        }

        if (text.length > 500) {
          return text.substring(0, 500);
        }

        return text;
      }
    }

    return 'Terjadi kesalahan pada server.';
  }

  // ============================================================
  // HELPER - EXTRACT DATA OBJECT
  // ============================================================

  Map<String, dynamic>? _extractObject(
    dynamic responseData,
  ) {
    responseData = _decodeIfString(responseData);

    if (responseData is! Map) {
      return null;
    }

    Map<String, dynamic> root =
        Map<String, dynamic>.from(responseData);

    dynamic data = root['data'];

    // ------------------------------------------------------------
    // data -> data -> object
    // ------------------------------------------------------------

    for (int i = 0; i < 5; i++) {
      if (data is Map) {
        final map = Map<String, dynamic>.from(data);

        if (map['data'] is Map) {
          data = map['data'];
        } else {
          break;
        }
      } else {
        break;
      }
    }

    // ------------------------------------------------------------
    // data object normal
    // ------------------------------------------------------------

    if (data is Map) {
      return Map<String, dynamic>.from(data);
    }

    // ------------------------------------------------------------
    // Backend langsung mengembalikan object contract
    // ------------------------------------------------------------

    if (root.containsKey('id') &&
        root.containsKey('listing_id') &&
        root.containsKey('farmer_id') &&
        root.containsKey('partner_id')) {
      return root;
    }

    return null;
  }

  // ============================================================
  // HELPER - EXTRACT LIST
  // ============================================================

  List<dynamic> _extractList(
    dynamic responseData,
  ) {
    responseData = _decodeIfString(responseData);

    if (responseData is! Map) {
      return const [];
    }

    final root =
        Map<String, dynamic>.from(responseData);

    dynamic data = root['data'];

    if (data is List) {
      return data;
    }

    if (data is Map) {
      final dataMap =
          Map<String, dynamic>.from(data);

      const keys = [
        'data',
        'market_listings',
        'listings',
        'market_offers',
        'offers',
        'purchase_contracts',
        'contracts',
        'items',
      ];

      for (final key in keys) {
        final nested = dataMap[key];

        if (nested is List) {
          return nested;
        }
      }
    }

    const rootKeys = [
      'market_listings',
      'listings',
      'market_offers',
      'offers',
      'purchase_contracts',
      'contracts',
      'items',
    ];

    for (final key in rootKeys) {
      final nested = root[key];

      if (nested is List) {
        return nested;
      }
    }

    return const [];
  }

  // ============================================================
  // HELPER - REQUEST JSON
  // ============================================================

  Future<Response<dynamic>> _getJson(
    String path, {
    Map<String, dynamic>? queryParameters,
  }) {
    return _apiClient.dio.get(
      path,
      queryParameters: queryParameters,
      options: Options(
        headers: const {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        responseType: ResponseType.plain,
        followRedirects: false,
        validateStatus: (status) {
          return status != null &&
              status >= 200 &&
              status < 400;
        },
      ),
    );
  }

  // ============================================================
  // MARKET LISTINGS
  // ============================================================

  Future<List<MarketListingModel>> fetchListings() async {
    try {
      final response = await _getJson(
        '/market-listings',
      );

      final status =
          response.statusCode ?? 0;

      if (status >= 300) {
        throw Exception(
          'Server mengarahkan request ke halaman lain. '
          'HTTP $status.',
        );
      }

      final responseData =
          _decodeIfString(response.data);

      if (responseData is! Map) {
        return [];
      }

      final data =
          _extractList(responseData);

      return data
          .whereType<Map>()
          .map(
            (item) =>
                MarketListingModel.fromJson(
              Map<String, dynamic>.from(item),
            ),
          )
          .toList();
    } on DioException catch (e) {
      debugPrint(
        '========== FETCH LISTINGS ERROR ==========\n'
        'STATUS: ${e.response?.statusCode}\n'
        'DATA: ${e.response?.data}\n'
        'MESSAGE: ${e.message}\n'
        '==========================================',
      );

      throw Exception(
        _errorMessage(e.response?.data),
      );
    }
  }

  // ============================================================
  // GET LISTING DETAIL
  // ============================================================

  Future<MarketListingModel> getListing(
    int id,
  ) async {
    try {
      final response = await _getJson(
        '/market-listings/$id',
      );

      final status =
          response.statusCode ?? 0;

      if (status >= 300) {
        throw Exception(
          'Gagal mengambil listing. HTTP $status.',
        );
      }

      final responseData =
          _decodeIfString(response.data);

      final root =
          _asMap(responseData);

      if (root == null) {
        throw Exception(
          'Response listing bukan JSON object.',
        );
      }

      dynamic data = root['data'];

      while (data is Map &&
          data['data'] is Map) {
        data = data['data'];
      }

      if (data == null &&
          root.containsKey('id')) {
        data = root;
      }

      if (data is! Map) {
        throw Exception(
          root['message']?.toString() ??
              'Data listing tidak ditemukan.',
        );
      }

      return MarketListingModel.fromJson(
        Map<String, dynamic>.from(data),
      );
    } on DioException catch (e) {
      debugPrint(
        '========== GET LISTING ERROR ==========\n'
        'STATUS: ${e.response?.statusCode}\n'
        'DATA: ${e.response?.data}\n'
        'MESSAGE: ${e.message}\n'
        '=======================================',
      );

      throw Exception(
        _errorMessage(e.response?.data),
      );
    }
  }

  // ============================================================
  // CREATE LISTING
  // ============================================================

  Future<MarketListingModel> createListing({
    required int farmId,
    required int cropSeasonId,
    required String commodity,
    required double quantity,
    required String unit,
    required double pricePerUnit,
    required XFile image,
    String? description,
    String? salesLink,
  }) async {
    try {
      final imageBytes =
          await image.readAsBytes();

      final imageFile =
          MultipartFile.fromBytes(
        imageBytes,
        filename: image.name.isEmpty
            ? 'listing-image.jpg'
            : image.name,
      );

      final formData =
          FormData.fromMap({
        'farm_id': farmId,
        'crop_season_id': cropSeasonId,
        'commodity': commodity,
        'quantity': quantity,
        'unit': unit,
        'price_per_unit': pricePerUnit,
        'description': description,
        'sales_link': salesLink,
        'image': imageFile,
      });

      final response =
          await _apiClient.dio.post(
        '/market-listings',
        data: formData,
        options: Options(
          contentType: 'multipart/form-data',
          headers: const {
            'Accept': 'application/json',
          },
          responseType: ResponseType.plain,
        ),
      );

      final responseData =
          _decodeIfString(response.data);

      final root =
          _asMap(responseData);

      if (root == null) {
        throw Exception(
          'Response server bukan JSON object.',
        );
      }

      if (root['success'] == false) {
        throw Exception(
          root['message']?.toString() ??
              'Gagal membuat listing.',
        );
      }

      final data =
          _extractObject(root);

      if (data == null) {
        throw Exception(
          'Data listing tidak ditemukan.',
        );
      }

      return MarketListingModel.fromJson(
        data,
      );
    } on DioException catch (e) {
      debugPrint(
        '========== CREATE LISTING ERROR ==========\n'
        'STATUS: ${e.response?.statusCode}\n'
        'DATA: ${e.response?.data}\n'
        'MESSAGE: ${e.message}\n'
        '==========================================',
      );

      throw Exception(
        _errorMessage(e.response?.data),
      );
    }
  }

  // ============================================================
  // CREATE OFFER
  // ============================================================

  Future<MarketOfferModel> createOffer({
    required int listingId,
    required double offeredPrice,
    required double quantity,
    String? message,
  }) async {
    try {
      final response =
          await _apiClient.dio.post(
        '/market-offers',
        data: {
          'listing_id': listingId,
          'offered_price': offeredPrice,
          'quantity': quantity,
          'message': message,
        },
        options: Options(
          headers: const {
            'Accept': 'application/json',
          },
          responseType: ResponseType.plain,
        ),
      );

      final responseData =
          _decodeIfString(response.data);

      final root =
          _asMap(responseData);

      if (root == null) {
        throw Exception(
          'Response server bukan JSON object.',
        );
      }

      if (root['success'] != true) {
        throw Exception(
          root['message']?.toString() ??
              'Gagal mengirim penawaran.',
        );
      }

      final data =
          _extractObject(root);

      if (data == null) {
        throw Exception(
          'Data penawaran tidak ditemukan.',
        );
      }

      return MarketOfferModel.fromJson(
        data,
      );
    } on DioException catch (e) {
      throw Exception(
        _errorMessage(e.response?.data),
      );
    }
  }

  // ============================================================
  // MY OFFERS
  // ============================================================

  Future<List<MarketOfferModel>>
      fetchMyOffers() async {
    try {
      final response =
          await _getJson('/market-offers');

      final status =
          response.statusCode ?? 0;

      if (status >= 300) {
        throw Exception(
          'Gagal mengambil penawaran. HTTP $status.',
        );
      }

      final responseData =
          _decodeIfString(response.data);

      final data =
          _extractList(responseData);

      return data
          .whereType<Map>()
          .map(
            (item) =>
                MarketOfferModel.fromJson(
              Map<String, dynamic>.from(item),
            ),
          )
          .toList();
    } on DioException catch (e) {
      throw Exception(
        _errorMessage(e.response?.data),
      );
    }
  }

  // ============================================================
  // LISTING OFFERS
  // ============================================================

  Future<List<MarketOfferModel>>
      fetchListingOffers(
    int listingId,
  ) async {
    try {
      final response =
          await _getJson(
        '/market-listings/$listingId/offers',
      );

      final status =
          response.statusCode ?? 0;

      if (status >= 300) {
        throw Exception(
          'Gagal mengambil penawaran listing. '
          'HTTP $status.',
        );
      }

      final responseData =
          _decodeIfString(response.data);

      final data =
          _extractList(responseData);

      return data
          .whereType<Map>()
          .map(
            (item) =>
                MarketOfferModel.fromJson(
              Map<String, dynamic>.from(item),
            ),
          )
          .toList();
    } on DioException catch (e) {
      throw Exception(
        _errorMessage(e.response?.data),
      );
    }
  }

  // ============================================================
  // UPDATE OFFER STATUS
  // ============================================================

  Future<MarketOfferModel>
      updateOfferStatus({
    required int offerId,
    required String status,
    double? counterPrice,
    double? counterQuantity,
    String? counterNotes,
  }) async {
    try {
      final payload =
          <String, dynamic>{
        'status': status,
      };

      if (counterPrice != null) {
        payload['counter_price'] =
            counterPrice;
      }

      if (counterQuantity != null) {
        payload['counter_quantity'] =
            counterQuantity;
      }

      if (counterNotes != null &&
          counterNotes.trim().isNotEmpty) {
        payload['counter_notes'] =
            counterNotes.trim();
      }

      final response =
          await _apiClient.dio.put(
        '/market-offers/$offerId',
        data: payload,
        options: Options(
          headers: const {
            'Accept': 'application/json',
          },
          responseType: ResponseType.plain,
        ),
      );

      final responseData =
          _decodeIfString(response.data);

      final root =
          _asMap(responseData);

      if (root == null) {
        throw Exception(
          'Response server bukan JSON object.',
        );
      }

      if (root['success'] != true) {
        throw Exception(
          root['message']?.toString() ??
              'Gagal memperbarui penawaran.',
        );
      }

      final data =
          _extractObject(root);

      if (data == null) {
        throw Exception(
          'Data penawaran tidak ditemukan.',
        );
      }

      return MarketOfferModel.fromJson(
        data,
      );
    } on DioException catch (e) {
      throw Exception(
        _errorMessage(e.response?.data),
      );
    }
  }

  // ============================================================
  // FETCH PURCHASE CONTRACTS
  // ============================================================

  Future<List<PurchaseContractModel>>
      fetchContracts() async {
    try {
      final response =
          await _getJson(
        '/purchase-contracts',
      );

      final status =
          response.statusCode ?? 0;

      if (status >= 300) {
        throw Exception(
          'Gagal mengambil kontrak. HTTP $status.',
        );
      }

      final responseData =
          _decodeIfString(response.data);

      final data =
          _extractList(responseData);

      return data
          .whereType<Map>()
          .map(
            (item) =>
                PurchaseContractModel.fromJson(
              Map<String, dynamic>.from(item),
            ),
          )
          .toList();
    } on DioException catch (e) {
      debugPrint(
        '========== FETCH CONTRACTS ERROR ==========\n'
        'STATUS: ${e.response?.statusCode}\n'
        'TYPE: ${e.response?.data.runtimeType}\n'
        'DATA: ${e.response?.data}\n'
        'MESSAGE: ${e.message}\n'
        '===========================================',
      );

      throw Exception(
        _errorMessage(e.response?.data),
      );
    }
  }

  // ============================================================
  // PARSE PURCHASE CONTRACT RESPONSE
  // ============================================================

  PurchaseContractModel _parseContractResponse(
    dynamic responseData, {
    required int contractId,
  }) {
    responseData =
        _decodeIfString(responseData);

    final root =
        _asMap(responseData);

    if (root == null) {
      throw Exception(
        'Response kontrak bukan JSON object.',
      );
    }

    // ----------------------------------------------------------
    // SUCCESS FALSE
    // ----------------------------------------------------------

    if (root['success'] == false) {
      throw Exception(
        root['message']?.toString() ??
            'Faktur pembelian tidak dapat diambil.',
      );
    }

    // ----------------------------------------------------------
    // AMBIL OBJECT CONTRACT
    // ----------------------------------------------------------

    final contractData =
        _extractObject(root);

    if (contractData == null) {
      throw Exception(
        root['message']?.toString() ??
            'Data faktur pembelian tidak ditemukan.',
      );
    }

    // ----------------------------------------------------------
    // VALIDASI ID
    // ----------------------------------------------------------

    final returnedId =
        contractData['id'];

    if (returnedId != null) {
      final parsedId =
          int.tryParse(
        returnedId.toString(),
      );

      if (parsedId != null &&
          parsedId != contractId) {
        throw Exception(
          'Data kontrak yang diterima tidak sesuai '
          'dengan nomor kontrak yang diminta.',
        );
      }
    }

    debugPrint(
      '========== CONTRACT PARSED ==========\n'
      'ID: ${contractData['id']}\n'
      'LISTING ID: ${contractData['listing_id']}\n'
      'FARMER ID: ${contractData['farmer_id']}\n'
      'PARTNER ID: ${contractData['partner_id']}\n'
      'QUANTITY: ${contractData['quantity']}\n'
      'AGREED PRICE: ${contractData['agreed_price']}\n'
      'TOTAL: ${contractData['total_amount']}\n'
      'STATUS: ${contractData['status']}\n'
      '=====================================',
    );

    return PurchaseContractModel.fromJson(
      contractData,
    );
  }

  // ============================================================
// GET PURCHASE CONTRACT / INVOICE
// ============================================================
//
// PENTING:
// Kita menggunakan endpoint:
//
// GET /purchase-contracts/{id}
//
// Endpoint ini sudah tersedia di PurchaseContractController::show()
// dan sudah mengembalikan JSON:
//
// {
//   "success": true,
//   "data": {
//      ...
//   }
// }
//
// Jangan menggunakan /invoice di Flutter karena endpoint tersebut
// pada environment sekarang mengembalikan HTML.
// ============================================================

Future<PurchaseContractModel> getContract(
  int contractId,
) async {
  if (contractId <= 0) {
    throw Exception('ID kontrak tidak valid.');
  }

  try {
    debugPrint('==========================================');
    debugPrint('GET PURCHASE CONTRACT / INVOICE');
    debugPrint('CONTRACT ID: $contractId');
    debugPrint('==========================================');

    /*
     * api.php:
     *
     * Route::prefix('v1')->group(...)
     *
     * Route:
     * purchase-contracts/{purchaseContract}/invoice
     *
     * Karena file tersebut adalah routes/api.php,
     * Laravel otomatis memberikan prefix /api.
     *
     * Endpoint final:
     *
     * /api/v1/purchase-contracts/{id}/invoice
     */

    final response = await _apiClient.dio.get(
      '/api/v1/purchase-contracts/$contractId/invoice',
      options: Options(
        headers: const {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        responseType: ResponseType.plain,
        followRedirects: false,
        validateStatus: (status) {
          return status != null &&
              status >= 200 &&
              status < 500;
        },
      ),
    );

    final statusCode = response.statusCode ?? 0;
    final contentType =
        response.headers.value('content-type') ?? '';

    dynamic responseData = response.data;

    debugPrint('==========================================');
    debugPrint('INVOICE RESPONSE');
    debugPrint('STATUS       : $statusCode');
    debugPrint('CONTENT TYPE : $contentType');
    debugPrint('TYPE         : ${responseData.runtimeType}');
    debugPrint('URI          : ${response.requestOptions.uri}');
    debugPrint('DATA         : $responseData');
    debugPrint('==========================================');

    /*
     * ----------------------------------------------------------
     * HTTP ERROR
     * ----------------------------------------------------------
     */

    if (statusCode < 200 || statusCode >= 300) {
      dynamic errorData = responseData;

      if (errorData is String) {
        try {
          errorData = jsonDecode(errorData);
        } catch (_) {}
      }

      if (errorData is Map) {
        final map =
            Map<String, dynamic>.from(errorData);

        throw Exception(
          map['message']?.toString() ??
              'Gagal mengambil faktur pembelian. '
                  'HTTP $statusCode',
        );
      }

      throw Exception(
        'Gagal mengambil faktur pembelian. '
        'HTTP $statusCode',
      );
    }

    /*
     * ----------------------------------------------------------
     * STRING -> JSON
     * ----------------------------------------------------------
     */

    if (responseData is String) {
      final raw = responseData.trim();

      if (raw.isEmpty) {
        throw Exception(
          'Server mengembalikan response kosong.',
        );
      }

      /*
       * Kalau server mengembalikan HTML,
       * jangan mencoba memasukkannya ke model.
       */

      final lower = raw.toLowerCase();

      if (lower.startsWith('<!doctype html') ||
          lower.startsWith('<html') ||
          lower.contains('<html')) {
        throw Exception(
          'Endpoint API masih mengembalikan HTML. '
          'Endpoint yang digunakan:\n'
          '/api/v1/purchase-contracts/$contractId/invoice',
        );
      }

      try {
        responseData = jsonDecode(raw);
      } catch (_) {
        throw Exception(
          'Server mengembalikan response bukan JSON valid.',
        );
      }
    }

    /*
     * ----------------------------------------------------------
     * ROOT HARUS JSON OBJECT
     * ----------------------------------------------------------
     */

    if (responseData is! Map) {
      throw Exception(
        'Response kontrak bukan JSON object.\n'
        'Tipe response: ${responseData.runtimeType}',
      );
    }

    final root =
        Map<String, dynamic>.from(responseData);

    debugPrint('========== INVOICE JSON ROOT ==========');
    debugPrint('$root');
    debugPrint('=======================================');

    /*
     * ----------------------------------------------------------
     * SUCCESS
     * ----------------------------------------------------------
     */

    if (root['success'] == false) {
      throw Exception(
        root['message']?.toString() ??
            'Faktur pembelian tidak dapat diambil.',
      );
    }

    /*
     * ----------------------------------------------------------
     * AMBIL DATA
     * ----------------------------------------------------------
     *
     * Response backend yang diharapkan:
     *
     * {
     *   "success": true,
     *   "message": "...",
     *   "data": {
     *      "id": 1,
     *      ...
     *   }
     * }
     *
     */

    dynamic data = root['data'];

    /*
     * Antisipasi jika Resource Laravel menghasilkan:
     *
     * data:
     *   data:
     *     {...}
     */

    while (data is Map &&
        data['data'] is Map) {
      data = data['data'];
    }

    /*
     * Antisipasi jika backend langsung
     * mengirim object contract.
     */

    if (data == null &&
        root.containsKey('id') &&
        root.containsKey('listing_id')) {
      data = root;
    }

    /*
     * ----------------------------------------------------------
     * DATA HARUS MAP
     * ----------------------------------------------------------
     */

    if (data is! Map) {
      throw Exception(
        root['message']?.toString() ??
            'Data faktur pembelian tidak ditemukan.',
      );
    }

    final contractJson =
        Map<String, dynamic>.from(data);

    debugPrint('========== INVOICE CONTRACT ==========');
    debugPrint('$contractJson');
    debugPrint('======================================');

    /*
     * ----------------------------------------------------------
     * JSON -> MODEL
     * ----------------------------------------------------------
     */

    final contract =
        PurchaseContractModel.fromJson(
      contractJson,
    );

    debugPrint('========== INVOICE MODEL =============');
    debugPrint('ID           : ${contract.id}');
    debugPrint('LISTING ID   : ${contract.listingId}');
    debugPrint('FARMER ID    : ${contract.farmerId}');
    debugPrint('PARTNER ID   : ${contract.partnerId}');
    debugPrint('QUANTITY     : ${contract.quantity}');
    debugPrint('PRICE        : ${contract.agreedPrice}');
    debugPrint('TOTAL        : ${contract.totalAmount}');
    debugPrint('STATUS       : ${contract.status}');
    debugPrint('FARMER       : ${contract.farmerName}');
    debugPrint('FARMER PHONE : ${contract.farmerPhone}');
    debugPrint('PARTNER      : ${contract.partnerName}');
    debugPrint('PARTNER PHONE: ${contract.partnerPhone}');
    debugPrint('======================================');

    return contract;
  } on DioException catch (e) {
    debugPrint('==========================================');
    debugPrint('INVOICE DIO ERROR');
    debugPrint('STATUS       : ${e.response?.statusCode}');
    debugPrint(
      'CONTENT TYPE : '
      '${e.response?.headers.value('content-type')}',
    );
    debugPrint(
      'TYPE         : '
      '${e.response?.data.runtimeType}',
    );
    debugPrint('DATA         : ${e.response?.data}');
    debugPrint('MESSAGE      : ${e.message}');
    debugPrint('URI          : ${e.requestOptions.uri}');
    debugPrint('==========================================');

    dynamic errorData = e.response?.data;

    if (errorData is String) {
      try {
        errorData = jsonDecode(errorData);
      } catch (_) {}
    }

    if (errorData is Map) {
      final map =
          Map<String, dynamic>.from(errorData);

      throw Exception(
        map['message']?.toString() ??
            'Gagal mengambil faktur pembelian.',
      );
    }

    throw Exception(
      'Gagal mengambil faktur pembelian. '
      'HTTP ${e.response?.statusCode ?? '-'}',
    );
  } catch (e) {
    debugPrint('==========================================');
    debugPrint('INVOICE GENERAL ERROR');
    debugPrint('ERROR: $e');
    debugPrint('TYPE: ${e.runtimeType}');
    debugPrint('==========================================');

    if (e is Exception) {
      rethrow;
    }

    throw Exception(
      'Gagal mengambil faktur pembelian.',
    );
  }
}

  // ============================================================
  // CREATE PURCHASE CONTRACT
  // ============================================================

  Future<PurchaseContractModel>
      createPurchaseContract({
    required int listingId,
    required double quantity,
    required double agreedPrice,
    String? notes,
  }) async {
    try {
      final response =
          await _apiClient.dio.post(
        '/purchase-contracts',
        data: {
          'listing_id': listingId,
          'quantity': quantity,
          'agreed_price': agreedPrice,
          'notes': notes,
        },
        options: Options(
          headers: const {
            'Accept': 'application/json',
          },
          responseType: ResponseType.plain,
        ),
      );

      final responseData =
          _decodeIfString(response.data);

      final root =
          _asMap(responseData);

      if (root == null) {
        throw Exception(
          'Response server bukan JSON object.',
        );
      }

      if (root['success'] != true) {
        throw Exception(
          root['message']?.toString() ??
              'Gagal membuat pesanan/kontrak.',
        );
      }

      final data =
          _extractObject(root);

      if (data == null) {
        throw Exception(
          'Data kontrak tidak ditemukan dalam respon.',
        );
      }

      return PurchaseContractModel.fromJson(
        data,
      );
    } on DioException catch (e) {
      debugPrint(
        '========== CREATE CONTRACT ERROR ==========\n'
        'STATUS: ${e.response?.statusCode}\n'
        'DATA: ${e.response?.data}\n'
        'MESSAGE: ${e.message}\n'
        '===========================================',
      );

      throw Exception(
        _errorMessage(e.response?.data),
      );
    }
  }

  // ============================================================
  // SALES REPORT
  // ============================================================

  Future<Map<String, dynamic>>
      fetchSalesReport({
    String period = 'all',
  }) async {
    try {
      final response =
          await _getJson(
        '/sales-report',
        queryParameters: {
          'period': period,
        },
      );

      final status =
          response.statusCode ?? 0;

      if (status >= 300) {
        throw Exception(
          'Gagal mengambil laporan penjualan. '
          'HTTP $status.',
        );
      }

      final responseData =
          _decodeIfString(response.data);

      final root =
          _asMap(responseData);

      if (root == null) {
        throw Exception(
          'Response laporan penjualan bukan JSON object.',
        );
      }

      if (root['success'] != true) {
        throw Exception(
          root['message']?.toString() ??
              'Gagal memuat laporan penjualan.',
        );
      }

      final data =
          root['data'];

      if (data is! Map) {
        return {};
      }

      return Map<String, dynamic>.from(
        data,
      );
    } on DioException catch (e) {
      debugPrint(
        'SALES REPORT ERROR: $e',
      );

      return {};
    } catch (e) {
      debugPrint(
        'SALES REPORT ERROR: $e',
      );

      return {};
    }
  }

  // ============================================================
  // CONTRACT PAYMENTS
  // ============================================================

  Future<List<Map<String, dynamic>>>
      fetchContractPayments() async {
    try {
      final response =
          await _getJson(
        '/contract-payments',
      );

      final status =
          response.statusCode ?? 0;

      if (status >= 300) {
        throw Exception(
          'Gagal mengambil pembayaran kontrak. '
          'HTTP $status.',
        );
      }

      final responseData =
          _decodeIfString(response.data);

      final root =
          _asMap(responseData);

      if (root == null) {
        return [];
      }

      final data =
          root['data'];

      if (data is! List) {
        return [];
      }

      return data
          .whereType<Map>()
          .map(
            (item) =>
                Map<String, dynamic>.from(item),
          )
          .toList();
    } on DioException catch (e) {
      throw Exception(
        _errorMessage(e.response?.data),
      );
    } catch (e) {
      throw Exception(
        e.toString().replaceFirst(
              'Exception: ',
              '',
            ),
      );
    }
  }
}