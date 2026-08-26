import 'package:dio/dio.dart';
import 'package:image_picker/image_picker.dart';

import 'package:padi/core/network/api_client.dart';
import 'package:padi/features/marketplace/data/models/market_listing_model.dart';
import 'package:padi/features/marketplace/data/models/market_offer_model.dart';
import 'package:padi/features/marketplace/data/models/purchase_contract_model.dart';

class MarketplaceApiService {
  const MarketplaceApiService(this._apiClient);

  final ApiClient _apiClient;

  Future<List<MarketListingModel>> fetchListings() async {
    final response = await _apiClient.dio.get('/market-listings');

    final responseData = response.data;

    if (responseData is! Map) {
      return [];
    }

    final data = _extractList(responseData);

    if (data.isEmpty) {
      return [];
    }

    return data
        .whereType<Map>()
        .map(
          (item) =>
              MarketListingModel.fromJson(Map<String, dynamic>.from(item)),
        )
        .toList();
  }

  List<dynamic> _extractList(Map<dynamic, dynamic> responseData) {
    final data = responseData['data'];

    if (data is List) {
      return data;
    }

    if (data is Map) {
      for (final key in [
        'data',
        'market_listings',
        'listings',
        'market_offers',
        'offers',
        'purchase_contracts',
        'contracts',
        'items',
      ]) {
        final nested = data[key];
        if (nested is List) {
          return nested;
        }
      }
    }

    for (final key in [
      'market_listings',
      'listings',
      'market_offers',
      'offers',
      'purchase_contracts',
      'contracts',
      'items',
    ]) {
      final nested = responseData[key];
      if (nested is List) {
        return nested;
      }
    }

    return const [];
  }

  Future<MarketListingModel> getListing(int id) async {
    try {
      final response = await _apiClient.dio.get('/market-listings/$id');

      final responseData = response.data;

      if (responseData is! Map) {
        throw Exception('Respons server tidak valid.');
      }

      final data = responseData['data'];

      if (data is! Map) {
        throw Exception(
          responseData['message']?.toString() ??
              'Data listing tidak ditemukan.',
        );
      }

      return MarketListingModel.fromJson(Map<String, dynamic>.from(data));
    } on DioException catch (e) {
      final data = e.response?.data;

      if (data is Map) {
        throw Exception(
          data['message']?.toString() ?? 'Gagal mengambil detail hasil panen.',
        );
      }

      throw Exception('Server error ${e.response?.statusCode ?? ''}.');
    }
  }

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
      final imageBytes = await image.readAsBytes();
      final imageFile = MultipartFile.fromBytes(
        imageBytes,
        filename: image.name.isEmpty ? 'listing-image.jpg' : image.name,
      );

      final formData = FormData.fromMap({
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

      final response = await _apiClient.dio.post(
        '/market-listings',
        data: formData,
        options: Options(contentType: 'multipart/form-data'),
      );

      final responseData = response.data;

      if (responseData is! Map) {
        throw Exception('Respons server tidak valid.');
      }

      if (responseData['success'] == false) {
        throw Exception(
          responseData['message']?.toString() ??
              'Gagal membuat listing hasil panen.',
        );
      }

      final data = responseData['data'];

      if (data is! Map) {
        throw Exception('Data listing tidak ditemukan.');
      }

      return MarketListingModel.fromJson(Map<String, dynamic>.from(data));
    } on DioException catch (e) {
      final responseData = e.response?.data;

      if (responseData is Map) {
        final message = responseData['message'];

        if (message != null && message.toString().trim().isNotEmpty) {
          throw Exception(message.toString());
        }

        final errors = responseData['errors'];

        if (errors is Map) {
          final messages = <String>[];

          for (final value in errors.values) {
            if (value is List) {
              messages.addAll(value.map((item) => item.toString()));
            } else {
              messages.add(value.toString());
            }
          }

          if (messages.isNotEmpty) {
            throw Exception(messages.join('\n'));
          }
        }
      }

      throw Exception('Server error ${e.response?.statusCode ?? ''}.');
    }
  }

  Future<MarketOfferModel> createOffer({
    required int listingId,
    required double offeredPrice,
    required double quantity,
    String? message,
  }) async {
    try {
      final response = await _apiClient.dio.post(
        '/market-offers',
        data: {
          'listing_id': listingId,
          'offered_price': offeredPrice,
          'quantity': quantity,
          'message': message,
        },
      );

      final responseData = response.data;

      if (responseData is! Map) {
        throw Exception('Respons server tidak valid.');
      }

      if (responseData['success'] != true) {
        throw Exception(
          responseData['message']?.toString() ?? 'Gagal mengirim penawaran.',
        );
      }

      final data = responseData['data'];

      if (data is! Map) {
        throw Exception('Data penawaran tidak ditemukan.');
      }

      return MarketOfferModel.fromJson(Map<String, dynamic>.from(data));
    } on DioException catch (e) {
      final data = e.response?.data;

      if (data is Map) {
        throw Exception(
          data['message']?.toString() ?? 'Server gagal memproses penawaran.',
        );
      }

      throw Exception('Server error ${e.response?.statusCode ?? ''}.');
    }
  }

  Future<List<MarketOfferModel>> fetchMyOffers() async {
    try {
      final response = await _apiClient.dio.get('/market-offers');

      final responseData = response.data;

      if (responseData is! Map) {
        return [];
      }

      final data = _extractList(responseData);

      if (data.isEmpty) {
        return [];
      }

      return data
          .whereType<Map>()
          .map(
            (item) =>
                MarketOfferModel.fromJson(Map<String, dynamic>.from(item)),
          )
          .toList();
    } on DioException catch (e) {
      final data = e.response?.data;

      if (data is Map) {
        throw Exception(
          data['message']?.toString() ?? 'Gagal mengambil penawaran.',
        );
      }

      throw Exception('Server error ${e.response?.statusCode ?? ''}.');
    }
  }

  Future<List<MarketOfferModel>> fetchListingOffers(int listingId) async {
    try {
      final response = await _apiClient.dio.get(
        '/market-listings/$listingId/offers',
      );

      final responseData = response.data;

      if (responseData is! Map) {
        throw Exception('Respons server tidak valid.');
      }

      final data = _extractList(responseData);

      if (data.isEmpty) {
        return [];
      }

      return data
          .whereType<Map>()
          .map(
            (item) =>
                MarketOfferModel.fromJson(Map<String, dynamic>.from(item)),
          )
          .toList();
    } on DioException catch (e) {
      final data = e.response?.data;

      if (data is Map) {
        throw Exception(
          data['message']?.toString() ?? 'Gagal mengambil penawaran.',
        );
      }

      throw Exception('Server error ${e.response?.statusCode ?? ''}.');
    }
  }

  Future<MarketOfferModel> updateOfferStatus({
    required int offerId,
    required String status,
  }) async {
    try {
      final response = await _apiClient.dio.put(
        '/market-offers/$offerId',
        data: {'status': status},
      );

      final responseData = response.data;

      if (responseData is! Map) {
        throw Exception('Respons server tidak valid.');
      }

      if (responseData['success'] != true) {
        throw Exception(
          responseData['message']?.toString() ?? 'Gagal memperbarui penawaran.',
        );
      }

      final data = responseData['data'];

      if (data is! Map) {
        throw Exception('Data penawaran tidak ditemukan.');
      }

      return MarketOfferModel.fromJson(Map<String, dynamic>.from(data));
    } on DioException catch (e) {
      final data = e.response?.data;

      if (data is Map) {
        throw Exception(
          data['message']?.toString() ?? 'Server gagal memproses penawaran.',
        );
      }

      throw Exception('Server error ${e.response?.statusCode ?? ''}.');
    }
  }

  Future<List<PurchaseContractModel>> fetchContracts() async {
    final response = await _apiClient.dio.get('/purchase-contracts');

    final responseData = response.data;

    if (responseData is! Map) {
      return [];
    }

    final data = _extractList(responseData);

    if (data.isEmpty) {
      return [];
    }

    return data
        .whereType<Map>()
        .map(
          (item) =>
              PurchaseContractModel.fromJson(Map<String, dynamic>.from(item)),
        )
        .toList();
  }

  Future<PurchaseContractModel> getContract(int contractId) async {
    final response = await _apiClient.dio.get(
      '/purchase-contracts/$contractId',
    );

    final responseData = response.data;

    if (responseData is! Map) {
      throw Exception('Respons server tidak valid.');
    }

    final data = responseData['data'];

    if (data is! Map) {
      throw Exception(
        responseData['message']?.toString() ?? 'Kontrak tidak ditemukan.',
      );
    }

    return PurchaseContractModel.fromJson(Map<String, dynamic>.from(data));
  }
}
