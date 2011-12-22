require 'digest/sha1'

class User < ActiveRecord::Base

  #has_one :client

	validates_presence_of :name
	validates_uniqueness_of :name
  validates_length_of :name, :minimum => 2
  validates_length_of :name, :maximum => 254
  validates_format_of :name, :with => /\A([^@\s]+)@((?:[-a-z0-9]+\.)+[a-z]{2,})\Z/i, :on => :create

	attr_accessor :password_confirmation
	validates_confirmation_of :password

	def validate
		errors.add_to_base("Missing password" ) if hashed_password.blank?
	end
	
	def self.authenticate(name, password)
		user = self.find_by_name(name)
 		if user
  			expected_password = encrypted_password(password, user.salt)
  			if user.hashed_password != expected_password
  				user = nil
  			end
		end
		user
	end
	
	# 'password' is a virtual attribute
	def password
		@password
	end
	
	def password=(pwd)
		@password = pwd
		return if pwd.blank?
		create_new_salt
		self.hashed_password = User.encrypted_password(self.password, self.salt)
  end

  def self.search(search)
    search_condition = "%" + search + "%"
    find(:all, :conditions => ['id LIKE ? OR name LIKE ?', search_condition, search_condition])
  end
	
	private
	
	def self.encrypted_password(password, salt)
		string_to_hash = password + "xxxx" + salt 
		Digest::SHA1.hexdigest(string_to_hash)
	end
	
	def create_new_salt
		self.salt = self.object_id.to_s + rand.to_s
	end

end